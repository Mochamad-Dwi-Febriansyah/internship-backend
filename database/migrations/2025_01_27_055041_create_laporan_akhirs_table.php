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
        Schema::create('laporan_akhirs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('UUID()')); 

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->uuid('berkas_id');
            $table->foreign('berkas_id')->references('id')->on('berkas')->onDelete('cascade');

            $table->uuid('master_sekolah_universitas_id');
            $table->foreign('master_sekolah_universitas_id')->references('id')->on('master_sekolah_universitas')->onDelete('cascade');

            $table->string('judul');
            $table->text('laporan');
            $table->string('file_laporan')->nullable();
            $table->string('foto')->nullable();
            $table->string('video')->nullable(); 
            $table->string('sertifikat')->nullable();
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('pending');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_akhirs');
    }
};
