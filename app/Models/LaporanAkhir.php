<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LaporanAkhir extends Model
{
    use HasUuids;
    protected $table = 'laporan_akhirs';
    protected $fillable = [
        'user_id',
        'berkas_id', 
        'master_sekolah_universitas_id', 
        'judul', 
        'laporan', 
        'file_laporan', 
        'foto', 
        'video', 
        'sertifikat', 
        'status'
    ];
}
