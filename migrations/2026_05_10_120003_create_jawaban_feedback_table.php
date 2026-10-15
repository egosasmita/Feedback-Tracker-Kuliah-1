<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_feedback', function (Blueprint $table) {
            $table->id('id_jawaban');
            
            $table->unsignedBigInteger('id_feedback');
            $table->unsignedBigInteger('id_pertanyaan');

            $table->integer('jawaban_skala')->nullable();
            $table->text('jawaban_teks')->nullable();

            $table->timestamps();

            $table->foreign('id_feedback')->references('id_feedback')->on('feedback')->onDelete('cascade');
            $table->foreign('id_pertanyaan')->references('id_pertanyaan')->on('pertanyaan_kuesioner')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_feedback');
    }
};