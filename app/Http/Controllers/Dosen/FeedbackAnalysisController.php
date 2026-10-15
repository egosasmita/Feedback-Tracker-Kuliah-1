<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedbackAnalysisController extends Controller
{
    // Menampilkan daftar Mata Kuliah yang diampu
    public function index()
    {
        $dosen = DB::table('dosen')->where('id_user', Auth::id())->first();

        $daftarMatkul = DB::table('jadwal')
            ->join('mata_kuliah', 'jadwal.id_matkul', '=', 'mata_kuliah.id_matkul')
            ->join('kelas', 'jadwal.id_kelas', '=', 'kelas.id_kelas')
            ->select('jadwal.id_jadwal', 'mata_kuliah.nama_matkul', 'kelas.nama_kelas')
            ->where('jadwal.id_dosen', $dosen->id_dosen)
            ->get();

        return view('dosen.feedback.index', compact('daftarMatkul'));
    }

    // Menampilkan Analisis Spesifik per Mata Kuliah
    public function show($id_jadwal, Request $request)
    {
        $dosen = DB::table('dosen')->where('id_user', Auth::id())->first();

        // 1. Info Jadwal
        $jadwal = DB::table('jadwal')
            ->join('mata_kuliah', 'jadwal.id_matkul', '=', 'mata_kuliah.id_matkul')
            ->join('kelas', 'jadwal.id_kelas', '=', 'kelas.id_kelas') // Join kelas
            ->where('id_jadwal', $id_jadwal)
            ->where('id_dosen', $dosen->id_dosen) // Pastikan dosen hanya akses jadwalnya
            ->select('jadwal.*', 'mata_kuliah.nama_matkul', 'kelas.nama_kelas') // Select nama_kelas
            ->first();

        if (!$jadwal) {
            abort(404, 'Jadwal tidak ditemukan atau Anda tidak memiliki akses.');
        }

        // 2. Statistik Ringkasan
        $totalResponden = DB::table('feedback')
            ->where('id_jadwal', $id_jadwal)
            ->distinct('id_mahasiswa')
            ->count('id_mahasiswa');

        $rataRataKeseluruhan = DB::table('jawaban_feedback')
            ->join('feedback', 'jawaban_feedback.id_feedback', '=', 'feedback.id_feedback')
            ->where('feedback.id_jadwal', $id_jadwal)
            ->whereNotNull('jawaban_skala')
            ->avg('jawaban_skala');

        // 3. Data Tren Mingguan
        $trenMingguanData = DB::table('jawaban_feedback')
            ->join('feedback', 'jawaban_feedback.id_feedback', '=', 'feedback.id_feedback')
            ->where('feedback.id_jadwal', $id_jadwal)
            ->whereNotNull('jawaban_skala')
            ->select(
                DB::raw('YEAR(feedback.tanggal_input) as tahun'),
                DB::raw('WEEK(feedback.tanggal_input, 1) as minggu'), // Mode 1: Senin hari pertama
                DB::raw('AVG(jawaban_feedback.jawaban_skala) as rata_rata_skor')
            )
            ->groupBy('tahun', 'minggu')
            ->orderBy('tahun', 'asc')
            ->orderBy('minggu', 'asc')
            ->get();

        $trenMingguan = [
            'labels' => $trenMingguanData->map(fn($item) => "Minggu Ke-{$item->minggu}, {$item->tahun}"),
            'data' => $trenMingguanData->pluck('rata_rata_skor'),
        ];

        // 4. Ambil semua pertanyaan yang relevan (default, kustom matkul, dan kustom dari semua pertemuan)
        $pertemuan_ids = DB::table('pertemuan')->where('id_jadwal', $id_jadwal)->pluck('id_pertemuan');

        $all_pertanyaan = DB::table('pertanyaan_kuesioner')
            ->where(function ($query) use ($jadwal) {
                // Pertanyaan umum (default) dan spesifik matkul (tapi bukan spesifik pertemuan)
                $query->whereNull('id_pertemuan')
                      ->where(function ($q) use ($jadwal) {
                          $q->whereNull('id_matkul')
                            ->orWhere('id_matkul', $jadwal->id_matkul);
                      });
            })
            // Ditambah semua pertanyaan spesifik dari semua pertemuan di jadwal ini
            ->orWhereIn('id_pertemuan', $pertemuan_ids)
            ->orderBy('id_pertemuan', 'asc')
            ->orderBy('id_matkul', 'asc')
            ->orderBy('created_at')
            ->get();

        // Pisahkan pertanyaan default dan kustom
        $pertanyaanDefault = $all_pertanyaan->where('id_matkul', null)->where('id_pertemuan', null);
        $pertanyaanKustom = $all_pertanyaan->where('id_matkul', $jadwal->id_matkul);

        // 5. Fungsi untuk mengambil semua jawaban dan agregasi hasilnya
        $processAnswers = function ($pertanyaanCollection, $id_jadwal) {
            foreach ($pertanyaanCollection as $p) {
                $query = DB::table('jawaban_feedback')
                    ->join('feedback', 'jawaban_feedback.id_feedback', '=', 'feedback.id_feedback')
                    ->where('feedback.id_jadwal', $id_jadwal)
                    ->where('jawaban_feedback.id_pertanyaan', $p->id_pertanyaan);

                if ($p->jenis_pertanyaan == 'skala') {
                    // Hitung rata-rata dan jumlah responden untuk pertanyaan skala
                    $p->statistik = (clone $query)->select(
                        DB::raw('AVG(jawaban_skala) as rata_rata'),
                        DB::raw('COUNT(jawaban_skala) as jumlah_jawaban')
                    )->first();
                } else {
                    // Ambil semua jawaban teks yang tidak kosong
                    $p->jawaban_teks = (clone $query)->whereNotNull('jawaban_teks')->pluck('jawaban_teks')->filter();
                }
            }
            return $pertanyaanCollection;
        };

        // Proses jawaban untuk kedua jenis pertanyaan
        $pertanyaanDefault = $processAnswers($pertanyaanDefault, $id_jadwal);
        $pertanyaanKustom = $processAnswers($pertanyaanKustom, $id_jadwal);

        return view('Dosen.feedback.show', compact(
            'jadwal',
            'pertanyaanDefault',
            'pertanyaanKustom',
            'totalResponden',
            'rataRataKeseluruhan',
            'trenMingguan'
        ));
    }
}