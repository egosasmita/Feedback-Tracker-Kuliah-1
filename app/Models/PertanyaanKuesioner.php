<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertanyaanKuesioner extends Model
{
    protected $table = 'pertanyaan_kuesioner';

    protected $primaryKey = 'id_pertanyaan';

    protected $fillable = [
        'teks_pertanyaan',
        'jenis_pertanyaan',
        'id_matkul',
        'id_pertemuan',
        'created_by_user_id'
    ];
}
