<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        // Ambil input filter prodi dari request GET
        $f_prodi = $request->input('f_prodi');

        // Query utama untuk laporan per Mata Kuliah
        $results = DB::table('jadwal')
            ->join('mata_kuliah', 'jadwal.id_matkul', '=', 'mata_kuliah.id_matkul')
            ->join('program_studi', 'mata_kuliah.id_prodi', '=', 'program_studi.id_prodi')
            ->join('kelas', 'jadwal.id_kelas', '=', 'kelas.id_kelas')
            ->leftJoin('feedback', 'jadwal.id_jadwal', '=', 'feedback.id_jadwal')
            ->leftJoin('jawaban_feedback', 'feedback.id_feedback', '=', 'jawaban_feedback.id_feedback')
            ->select(
                'mata_kuliah.nama_matkul',
                'mata_kuliah.semester',
                'mata_kuliah.sks',
                'kelas.nama_kelas',
                'program_studi.nama_prodi',
                'jadwal.id_jadwal',
                DB::raw('COUNT(DISTINCT feedback.id_mahasiswa) as total_responden'),
                DB::raw('AVG(jawaban_feedback.jawaban_skala) as rata_rata_skor')
            )
            ->when($f_prodi, function ($query, $f_prodi) {
                return $query->where('mata_kuliah.id_prodi', $f_prodi);
            })
            ->groupBy(
                'jadwal.id_jadwal',
                'mata_kuliah.nama_matkul',
                'mata_kuliah.semester',
                'mata_kuliah.sks',
                'kelas.nama_kelas',
                'program_studi.nama_prodi'
            )
            ->get();

        // Ambil daftar prodi untuk isi dropdown filter
        $prodi = DB::table('program_studi')->get();

        // Ambil daftar mata kuliah untuk isi dropdown filter
        $matkuls = DB::table('mata_kuliah')->get();
        $f_matkul = $request->input('f_matkul');

        return view('Admin.feedback.index', compact('results', 'prodi', 'matkuls', 'f_prodi', 'f_matkul'));
    }
    public function show(Request $request, $id_jadwal)
    {
        // 1. Ambil info jadwal & matkul
        $jadwal = DB::table('jadwal')
            ->join('mata_kuliah', 'jadwal.id_matkul', '=', 'mata_kuliah.id_matkul')
            ->join('dosen', 'jadwal.id_dosen', '=', 'dosen.id_dosen')
            ->join('users', 'dosen.id_user', '=', 'users.id_user')
            ->select('jadwal.*', 'mata_kuliah.nama_matkul', 'users.nama as nama_dosen')
            ->where('jadwal.id_jadwal', $id_jadwal)
            ->first();

        if (!$jadwal) {
            abort(404, 'Jadwal tidak ditemukan');
        }

        // 2. Ambil semua pertanyaan yang relevan (default, kustom matkul, dan kustom pertemuan)
        $pertemuan_ids = DB::table('pertemuan')->where('id_jadwal', $id_jadwal)->pluck('id_pertemuan');

        $pertanyaan = DB::table('pertanyaan_kuesioner')
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

        // 3. Ambil semua jawaban untuk jadwal ini dan agregasi hasilnya
        foreach ($pertanyaan as $p) {
            $query = DB::table('jawaban_feedback')
                ->join('feedback', 'jawaban_feedback.id_feedback', '=', 'feedback.id_feedback')
                ->where('feedback.id_jadwal', $id_jadwal)
                ->where('jawaban_feedback.id_pertanyaan', $p->id_pertanyaan);

            if ($p->jenis_pertanyaan == 'skala') {
                // Hitung rata-rata dan jumlah responden untuk pertanyaan skala
                $p->statistik = $query->select(DB::raw('AVG(jawaban_skala) as rata_rata, COUNT(*) as jumlah_jawaban'))->first();
            } else {
                // Ambil semua jawaban teks
                $p->jawaban_teks = $query->pluck('jawaban_teks')->filter();
            }
        }

        return view('Admin.feedback.show', compact('jadwal', 'pertanyaan'));
    }
}