<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Pertemuan; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PertemuanController extends Controller
{
    public function index($id_jadwal)
    {
        $jadwal = DB::table('jadwal')
            ->join('mata_kuliah', 'jadwal.id_matkul', '=', 'mata_kuliah.id_matkul')
            ->join('kelas', 'jadwal.id_kelas', '=', 'kelas.id_kelas')
            ->where('id_jadwal', $id_jadwal)
            ->first();

        $pertemuan = DB::table('pertemuan')
            ->where('id_jadwal', $id_jadwal)
            ->orderBy('pertemuan_ke', 'asc')
            ->get();

        return view('dosen.pertemuan.index', compact('jadwal', 'pertemuan'));
    }

    public function apiIndex($id_jadwal)
    {
        $data = Pertemuan::where('id_jadwal', $id_jadwal)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_jadwal' => 'required|exists:jadwal,id_jadwal',
            'pertemuan_ke' => 'required|integer|min:1',
            'materi' => 'required|string',
            'pertanyaan_kustom' => 'nullable|array',
            'pertanyaan_kustom.*.teks' => 'required_with:pertanyaan_kustom|string|max:255',
            'pertanyaan_kustom.*.jenis' => 'required_with:pertanyaan_kustom|in:skala,teks',
        ]);

        $jadwal = DB::table('jadwal')->where('id_jadwal', $request->id_jadwal)->first();

        DB::transaction(function () use ($request, $jadwal) {
            $id_pertemuan = DB::table('pertemuan')->insertGetId([
                'id_jadwal' => $request->id_jadwal,
                'pertemuan_ke' => $request->pertemuan_ke,
                'materi' => $request->materi,
                'tanggal' => Carbon::now()->toDateString(),
                'status' => 'terbuka',
                'created_at' => Carbon::now(),
            ]);

            if ($request->has('pertanyaan_kustom') && is_array($request->pertanyaan_kustom)) {
                $pertanyaanToInsert = [];
                foreach ($request->pertanyaan_kustom as $p) {
                    // Pastikan ada teks dan jenis sebelum menambahkan
                    if (!empty($p['teks']) && !empty($p['jenis'])) {
                        $pertanyaanToInsert[] = [
                            'teks_pertanyaan' => $p['teks'],
                            'jenis_pertanyaan' => $p['jenis'],
                            'id_matkul' => $jadwal->id_matkul, // Link ke matkul juga
                            'id_pertemuan' => $id_pertemuan, // Link ke pertemuan spesifik
                            'created_by_user_id' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if (!empty($pertanyaanToInsert)) {
                    DB::table('pertanyaan_kuesioner')->insert($pertanyaanToInsert);
                }
            }
        });

        return back()->with('success', 'Pertemuan berhasil dibuka. Jika ada, pertanyaan kustom telah ditambahkan.');
    }
}