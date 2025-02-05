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
        Schema::create('berkas', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('UUID()')); 

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
             
            $table->uuid('master_sekolah_universitas_id');
            $table->foreign('master_sekolah_universitas_id')->references('id')->on('master_sekolah_universitas')->onDelete('cascade');

            $table->uuid('mentor_id')->nullable();
            $table->foreign('mentor_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('nomor_registrasi', 50)->unique();
            $table->string('foto_identitas', 255);
            $table->string('surat_permohonan', 255); 
            $table->string('surat_diterima', 255)->nullable();
            $table->enum('status_berkas', ['terima', 'pending', 'tolak'])->default('pending');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
