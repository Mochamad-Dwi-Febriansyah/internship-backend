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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('UUID()')); 
            $table->uuid('user_id')->default(DB::raw('UUID()'))->nullable();
            $table->string('nama')->nullable();
            $table->string('action');
            $table->string('model')->nullable();
            $table->uuid('model_id')->nullable(); // ID model yang terpengaruh
            $table->json('changes')->nullable();
            $table->ipAddress('ip_address')->nullable(); // Alamat IP pengguna
            $table->text('user_agent')->nullable(); // Informasi perangkat pengguna
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
