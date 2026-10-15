<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pertanyaan_kuesioner', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pertemuan')->nullable()->after('id_matkul');
            $table->foreign('id_pertemuan')->references('id_pertemuan')->on('pertemuan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertanyaan_kuesioner', function (Blueprint $table) {
            $table->dropForeign(['id_pertemuan']);
            $table->dropColumn('id_pertemuan');
        });
    }
};
