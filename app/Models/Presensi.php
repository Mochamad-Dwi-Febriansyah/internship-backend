<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasUuids;
    protected $table = 'presensis';
    protected $fillable = [
        'user_id',
        'berkas_id',
       'tanggal',
        'waktu_check_in',
        'waktu_check_out',
        'foto_check_in',
        'foto_check_out',
        'keterangan',
        'latitude',
        'longitude',
        'status'
    ];

    public function laporanHarians()
{
    return $this->hasMany(LaporanHarian::class, 'presensi_id');
}
}
