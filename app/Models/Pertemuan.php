<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pertemuan extends Model
{
    use HasFactory;

    protected $table = 'pertemuan';   // nama tabel
    protected $primaryKey = 'id_pertemuan'; // primary key

    protected $fillable = [
        'id_jadwal',
        'pertemuan_ke',
        'materi',
        'tanggal',
        'status',
    ];
}
