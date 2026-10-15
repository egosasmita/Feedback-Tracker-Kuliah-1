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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'foto_profil')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('foto_profil')->nullable()->after('status_aktif');
            });
        }

        if (Schema::hasTable('dosen') && !Schema::hasColumn('dosen', 'foto_profil')) {
            Schema::table('dosen', function (Blueprint $table) {
                $table->string('foto_profil')->nullable()->after('nip');
            });
        }

        if (Schema::hasTable('mahasiswa') && !Schema::hasColumn('mahasiswa', 'foto_profil')) {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->string('foto_profil')->nullable()->after('nim');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'foto_profil')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('foto_profil');
            });
        }

        if (Schema::hasTable('dosen') && Schema::hasColumn('dosen', 'foto_profil')) {
            Schema::table('dosen', function (Blueprint $table) {
                $table->dropColumn('foto_profil');
            });
        }

        if (Schema::hasTable('mahasiswa') && Schema::hasColumn('mahasiswa', 'foto_profil')) {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->dropColumn('foto_profil');
            });
        }
    }
};
