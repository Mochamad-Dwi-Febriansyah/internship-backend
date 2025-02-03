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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_depan', 50)->nullable();
            // $table->string('nama_depan', 50);
            $table->string('nama_belakang', 50)->nullable();
            $table->string('nisn_npm_nim', 20)->nullable()->index();
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['male', 'female']);
            $table->string('nomor_hp', 20);
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->text('alamat');
            $table->string('kabupaten_kota', 100);
            $table->string('provinsi', 100);
            $table->string('kode_pos', 100); 
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
