<?php

namespace App\Models;

use App\Models\Jadwal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';
    protected $primaryKey = 'id_matkul';
    public $timestamps = false;
    protected $fillable = [
        'id_prodi',
        'nama_matkul',
        'semester',
        'sks',
    ];

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'id_matkul', 'id_matkul');
    }
}
