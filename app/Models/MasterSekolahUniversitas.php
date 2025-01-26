<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MasterSekolahUniversitas extends Model
{
    use HasUuids;
    protected $table = 'master_sekolah_universitas';
    protected $fillable = [
        'nama_sekolah_universitas',
        'jurusan_sekolah',
        'fakultas_universitas',
        'program_studi_universitas',
        'alamat_sekolah_universitas',
        'kabupaten_kota_sekolah_universitas',
        'provinsi_sekolah_universitas',
        'kode_pos_sekolah_universitas',
        'nomor_telp_sekolah_universitas',
        'email_sekolah_universitas',
    ];
}
