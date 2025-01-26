<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_sekolah_universitas', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('UUID()'));  
            $table->string('nama_sekolah_universitas', 100);
            $table->string('jurusan_sekolah', 100)->nullable();
            $table->string('fakultas_universitas', 100)->nullable();
            $table->string('program_studi_universitas', 100)->nullable();
            $table->text('alamat_sekolah_universitas');
            $table->string('kabupaten_kota_sekolah_universitas', 100);
            $table->string('provinsi_sekolah_universitas', 100);
            $table->string('kode_pos_sekolah_universitas', 10);
            $table->string('nomor_telp_sekolah_universitas', 20)->nullable();
            $table->string('email_sekolah_universitas', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_sekolah_universitas');
    }
};
