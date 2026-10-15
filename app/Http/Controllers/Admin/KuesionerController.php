<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KuesionerController extends Controller
{
    /**
     * Menampilkan halaman manajemen kuesioner default.
     */
    public function index()
    {
        $pertanyaanDefault = DB::table('pertanyaan_kuesioner')
            ->whereNull('id_matkul')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Admin.kuesioner.index', compact('pertanyaanDefault'));
    }

    /**
     * Menyimpan pertanyaan default baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'teks_pertanyaan' => 'required|string',
            'jenis_pertanyaan' => 'required|in:skala,teks',
        ]);

        DB::table('pertanyaan_kuesioner')->insert([
            'teks_pertanyaan' => $request->teks_pertanyaan,
            'jenis_pertanyaan' => $request->jenis_pertanyaan,
            'id_matkul' => null, // Pertanyaan default
            'created_by_user_id' => null, // Dibuat oleh sistem/admin
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.kuesioner.index')->with('success', 'Pertanyaan default berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit pertanyaan default.
     * Parameter $kuesioner adalah id_pertanyaan dari route resource.
     */
    public function edit(int $kuesioner)
    {
        $pertanyaan = DB::table('pertanyaan_kuesioner')
            ->where('id_pertanyaan', $kuesioner)
            ->whereNull('id_matkul') // Pastikan ini pertanyaan default
            ->first();

        if (!$pertanyaan) {
            return redirect()->route('admin.kuesioner.index')->withErrors(['error' => 'Pertanyaan default tidak ditemukan.']);
        }

        return view('Admin.kuesioner.edit', compact('pertanyaan'));
    }

    /**
     * Memperbarui pertanyaan default.
     * Parameter $kuesioner adalah id_pertanyaan dari route resource.
     */
    public function update(Request $request, int $kuesioner)
    {
        $request->validate([
            'teks_pertanyaan' => 'required|string',
            'jenis_pertanyaan' => 'required|in:skala,teks',
        ]);

        $pertanyaanQuery = DB::table('pertanyaan_kuesioner')
            ->where('id_pertanyaan', $kuesioner)
            ->whereNull('id_matkul');

        if (!$pertanyaanQuery->exists()) {
            return redirect()->route('admin.kuesioner.index')->withErrors(['error' => 'Pertanyaan default tidak ditemukan.']);
        }

        $pertanyaanQuery->update([
            'teks_pertanyaan' => $request->teks_pertanyaan,
            'jenis_pertanyaan' => $request->jenis_pertanyaan,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.kuesioner.index')->with('success', 'Pertanyaan default berhasil diperbarui.');
    }

    /**
     * Menghapus pertanyaan default.
     * Parameter $kuesioner adalah id_pertanyaan dari route resource.
     */
    public function destroy(int $kuesioner)
    {
        $pertanyaanQuery = DB::table('pertanyaan_kuesioner')->where('id_pertanyaan', $kuesioner)->whereNull('id_matkul');

        if (!$pertanyaanQuery->exists()) {
            return redirect()->route('admin.kuesioner.index')->withErrors(['error' => 'Pertanyaan default tidak ditemukan.']);
        }
        
        // Validasi: Jangan hapus jika sudah ada jawaban
        $hasJawaban = DB::table('jawaban_feedback')->where('id_pertanyaan', $kuesioner)->exists();
        if ($hasJawaban) {
            return redirect()->route('admin.kuesioner.index')->withErrors(['error' => 'Gagal menghapus. Pertanyaan ini sudah memiliki jawaban dari mahasiswa.']);
        }

        $pertanyaanQuery->delete();

        return redirect()->route('admin.kuesioner.index')->with('success', 'Pertanyaan default berhasil dihapus.');
    }
}