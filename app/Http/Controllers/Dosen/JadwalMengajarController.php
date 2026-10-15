<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalMengajarController extends Controller
{
    public function index()
    {
        // 1. Ambil data dosen yang sedang login dari relasi User
        // Pastikan relasi `dosen()` sudah didefinisikan di model User.
        $dosen = Auth::user()->dosen;

        if (!$dosen) {
            return redirect()->back()->with('error', 'Data dosen tidak ditemukan.');
        }

        // 2. Ambil jadwal menggunakan Eloquent dengan Eager Loading
        // Pastikan model Jadwal, MataKuliah, dan Kelas sudah dibuat
        // dan relasi (misal: mataKuliah() & kelas()) sudah didefinisikan di model Jadwal.
        $jadwal = Jadwal::with(['mataKuliah', 'kelas'])
            ->where('id_dosen', $dosen->id_dosen)
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->get();

        return view('dosen.jadwal.index', compact('jadwal'));
    }
}