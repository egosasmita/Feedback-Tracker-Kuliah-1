<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeedbackController extends Controller
{
    public function create($id_pertemuan)
    {
        $pertemuan = DB::table('pertemuan')
            ->join('jadwal', 'pertemuan.id_jadwal', '=', 'jadwal.id_jadwal')
            ->join('mata_kuliah', 'jadwal.id_matkul', '=', 'mata_kuliah.id_matkul')
            ->join('dosen', 'jadwal.id_dosen', '=', 'dosen.id_dosen')
            ->join('users', 'dosen.id_user', '=', 'users.id_user')
            // Ambil juga id_matkul untuk query pertanyaan
            ->select('pertemuan.*', 'mata_kuliah.nama_matkul', 'mata_kuliah.id_matkul', 'users.nama as nama_dosen')
            ->where('id_pertemuan', $id_pertemuan)
            ->first();

        if (!$pertemuan || $pertemuan->status !== 'terbuka') {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Sesi feedback tidak tersedia.');
        }

        // Ambil pertanyaan default, pertanyaan kustom untuk matkul ini, dan pertanyaan kustom untuk pertemuan ini
        $pertanyaan = DB::table('pertanyaan_kuesioner')
            ->where(function ($query) use ($pertemuan) {
                // Pertanyaan umum (default) dan spesifik matkul (tapi bukan spesifik pertemuan)
                $query->whereNull('id_pertemuan')
                      ->where(function ($q) use ($pertemuan) {
                          $q->whereNull('id_matkul')
                            ->orWhere('id_matkul', $pertemuan->id_matkul);
                      });
            })
            // Ditambah pertanyaan spesifik untuk pertemuan ini
            ->orWhere('id_pertemuan', $id_pertemuan)
            ->orderBy('id_pertemuan', 'asc') // Pertanyaan umum/matkul dulu (NULL), baru spesifik pertemuan
            ->orderBy('id_matkul', 'asc') // Pertanyaan default dulu (NULL), baru spesifik matkul
            ->orderBy('created_at')
            ->get();

        // Skala penilaian untuk pertanyaan tipe 'skala'
        $skala = DB::table('skala_penilaian')->orderBy('nilai', 'asc')->get();
        return view('Mahasiswa.feedback.create', compact('pertemuan', 'pertanyaan', 'skala'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $mahasiswa = DB::table('mahasiswa')->where('id_user', $user->id_user)->first();
        
        $pertemuan = DB::table('pertemuan')->where('id_pertemuan', $request->id_pertemuan)->first();

        // Validasi: pastikan mahasiswa belum mengisi
        $sudahIsi = DB::table('feedback')
            ->where('id_pertemuan', $request->id_pertemuan)
            ->where('id_mahasiswa', $mahasiswa->id_mahasiswa)
            ->exists();

        if ($sudahIsi) {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Anda sudah mengisi feedback untuk sesi ini.');
        }

        $request->validate([
            'jawaban' => 'required|array',
            'jawaban.*.skala' => 'nullable|numeric|between:1,5',
            'jawaban.*.teks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $mahasiswa, $pertemuan) {
            // 1. Buat header feedback
            $id_feedback = DB::table('feedback')->insertGetId([
                'id_pertemuan' => $request->id_pertemuan,
                'id_mahasiswa' => $mahasiswa->id_mahasiswa,
                'id_jadwal' => $pertemuan->id_jadwal,
                'tanggal_input' => now(),
            ]);

            // 2. Simpan setiap jawaban
            foreach ($request->jawaban as $id_pertanyaan => $jawaban) {
                DB::table('jawaban_feedback')->insert([
                    'id_feedback' => $id_feedback,
                    'id_pertanyaan' => $id_pertanyaan,
                    'jawaban_skala' => $jawaban['skala'] ?? null,
                    'jawaban_teks' => $jawaban['teks'] ?? null,
                ]);
            }
        });

        return redirect()->route('mahasiswa.dashboard')->with('success', 'Feedback berhasil disimpan!');
    }
}