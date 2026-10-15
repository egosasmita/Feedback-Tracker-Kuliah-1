<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            if (Schema::hasColumn('feedback', 'id_skala')) {
                // Coba hapus foreign key jika ada (best-effort)
                try {
                    $table->dropForeign(['id_skala']);
                } catch (\Exception $e) {
                    // Lanjutkan jika foreign key tidak ada
                }
                $table->dropColumn('id_skala');
            }
            if (Schema::hasColumn('feedback', 'komentar')) {
                $table->dropColumn('komentar');
            }
        });
    }

    public function down(): void
    {
        // Rollback: Tambahkan kembali kolom yang dihapus
        Schema::table('feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('feedback', 'id_skala')) {
                $table->unsignedBigInteger('id_skala')->nullable()->after('id_jadwal');
            }
            if (!Schema::hasColumn('feedback', 'komentar')) {
                $table->text('komentar')->nullable()->after('id_skala');
            }
        });
    }
};