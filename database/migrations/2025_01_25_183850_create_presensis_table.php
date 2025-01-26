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
        Schema::create('presensis', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('UUID()')); 

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->uuid('berkas_id');
            $table->foreign('berkas_id')->references('id')->on('berkas')->onDelete('cascade');

            $table->date('tanggal');
            $table->time('waktu_check_in');
            $table->string('foto_check_in', 255); 
            $table->time('waktu_check_out');
            $table->string('foto_check_out', 255);
            $table->text('keterangan');
            $table->decimal('latitude', 10, 6); // akurasi sekitar 1 meter   
            $table->decimal('longitude', 10, 6); 
            $table->enum('status', ['hadir', 'izin', 'sakit','alpa']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
