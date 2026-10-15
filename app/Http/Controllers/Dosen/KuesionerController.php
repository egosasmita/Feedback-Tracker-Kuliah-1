<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KuesionerController extends Controller
{
    public function index(Request $request)
    {
        $dosen = DB::table('dosen')->where('id_user', Auth::id())->first();
        if (!$dosen) {
            return redirect()->route('login')->withErrors(['error' => 'Profil dosen tidak ditemukan.']);
        }
        $f_matkul = $request->input('f_matkul');

        // 1. Ambil daftar mata kuliah yang diampu dosen
        $matkulDiampu = DB::table('jadwal')
            ->join('mata_kuliah', 'jadwal.id_matkul', '=', 'mata_kuliah.id_matkul')
            ->where('jadwal.id_dosen', $dosen->id_dosen)
            ->select('mata_kuliah.id_matkul', 'mata_kuliah.nama_matkul')
            ->distinct()
            ->get();

        $pertanyaanDefault = [];
        $pertanyaanKustom = [];
        $selectedMatkul = null;

        if ($f_matkul) {
            // 2. Ambil pertanyaan default dari Admin
            $pertanyaanDefault = DB::table('pertanyaan_kuesioner')
                ->whereNull('id_matkul')
                ->get();

            // 3. Ambil pertanyaan kustom untuk matkul yang dipilih & milik dosen ini
            $pertanyaanKustom = DB::table('pertanyaan_kuesioner')
                ->where('id_matkul', $f_matkul)
                ->get();

            $selectedMatkul = DB::table('mata_kuliah')->where('id_matkul', $f_matkul)->first();
        }

        return view('Dosen.kuesioner.index', compact('matkulDiampu', 'pertanyaanDefault', 'pertanyaanKustom', 'f_matkul', 'selectedMatkul'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_matkul' => 'required|exists:mata_kuliah,id_matkul',
            'teks_pertanyaan' => 'required|string',
            'jenis_pertanyaan' => 'required|in:skala,teks',
        ]);

        $dosen = DB::table('dosen')->where('id_user', Auth::id())->first();

        // Validasi: Pastikan dosen ini memang mengampu matkul tersebut
        $isPengampu = DB::table('jadwal')
            ->where('id_dosen', $dosen->id_dosen)
            ->where('id_matkul', $request->id_matkul)
            ->exists();

        if (!$isPengampu) {
            return redirect()->back()->withErrors(['error' => 'Anda tidak mengampu mata kuliah ini.']);
        }

        DB::table('pertanyaan_kuesioner')->insert([
            'teks_pertanyaan' => $request->teks_pertanyaan,
            'jenis_pertanyaan' => $request->jenis_pertanyaan,
            'id_matkul' => $request->id_matkul, // Pertanyaan kustom
            'created_by_user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dosen.kuesioner.index', ['f_matkul' => $request->id_matkul])->with('success', 'Pertanyaan kustom berhasil ditambahkan.');
    }

    public function edit(int $id_pertanyaan)
    {
        $pertanyaan = DB::table('pertanyaan_kuesioner')->where('id_pertanyaan', $id_pertanyaan)->first();

        if (!$pertanyaan) {
            return redirect()->route('dosen.kuesioner.index')->withErrors(['error' => 'Pertanyaan tidak ditemukan.']);
        }

        // Validasi: Pastikan hanya pembuat pertanyaan yang bisa mengedit
        if ($pertanyaan->created_by_user_id != Auth::id()) {
             return redirect()->route('dosen.kuesioner.index')->withErrors(['error' => 'Anda tidak memiliki hak untuk mengubah pertanyaan ini.']);
        }

        return view('Dosen.kuesioner.edit', compact('pertanyaan'));
    }

    public function update(Request $request, int $id_pertanyaan)
    {
        $request->validate([
            'teks_pertanyaan' => 'required|string',
            'jenis_pertanyaan' => 'required|in:skala,teks',
        ]);

        $pertanyaan = DB::table('pertanyaan_kuesioner')->where('id_pertanyaan', $id_pertanyaan)->first();

        // Validasi tambahan untuk memastikan user tidak mengubah pertanyaan milik orang lain
        if (!$pertanyaan || $pertanyaan->created_by_user_id != Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'Anda tidak berhak mengubah pertanyaan ini atau pertanyaan tidak ditemukan.']);
        }

        DB::table('pertanyaan_kuesioner')->where('id_pertanyaan', $id_pertanyaan)->update([
            'teks_pertanyaan' => $request->teks_pertanyaan,
            'jenis_pertanyaan' => $request->jenis_pertanyaan,
            'updated_at' => now(),
        ]);

        return redirect()->route('dosen.kuesioner.index', ['f_matkul' => $pertanyaan->id_matkul])->with('success', 'Pertanyaan kustom berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $pertanyaan = DB::table('pertanyaan_kuesioner')->where('id_pertanyaan', $id)->first();

        if (!$pertanyaan) {
            return redirect()->back()->withErrors(['error' => 'Pertanyaan tidak ditemukan.']);
        }

        // Validasi: Pastikan hanya pembuat pertanyaan yang bisa menghapus
        if ($pertanyaan->created_by_user_id != Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'Anda tidak memiliki hak untuk menghapus pertanyaan ini.']);
        }

        // Validasi tambahan: Cek apakah sudah ada jawaban
        $hasJawaban = DB::table('jawaban_feedback')->where('id_pertanyaan', $id)->exists();
        if ($hasJawaban) {
            return redirect()->route('dosen.kuesioner.index', ['f_matkul' => $pertanyaan->id_matkul])->withErrors(['error' => 'Gagal menghapus. Pertanyaan ini sudah memiliki jawaban dari mahasiswa.']);
        }

        DB::table('pertanyaan_kuesioner')->where('id_pertanyaan', $id)->delete();
        return redirect()->route('dosen.kuesioner.index', ['f_matkul' => $pertanyaan->id_matkul])->with('success', 'Pertanyaan kustom berhasil dihapus.');
    }
}
