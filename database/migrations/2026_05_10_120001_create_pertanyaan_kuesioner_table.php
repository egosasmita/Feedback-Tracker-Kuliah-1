<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertanyaan_kuesioner', function (Blueprint $table) {
            $table->id('id_pertanyaan');
            $table->text('teks_pertanyaan');
            $table->enum('jenis_pertanyaan', ['skala', 'teks'])->default('skala');
            
            // Jika id_matkul NULL, ini pertanyaan default (Admin)
            // Jika terisi, ini pertanyaan kustom (Dosen)
            $table->unsignedBigInteger('id_matkul')->nullable();
            
            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->timestamps();

            $table->foreign('id_matkul')->references('id_matkul')->on('mata_kuliah')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id_user')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertanyaan_kuesioner');
    }
};