<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LaporanHarian extends Model
{
    use HasUuids;
    protected $table = 'laporan_harians';
    protected $fillable = [
        'user_id',
        'presensi_id', 
        'judul', 
        'laporan', 
        'foto', 
        'status'
    ];
}
