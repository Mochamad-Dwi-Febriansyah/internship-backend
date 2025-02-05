<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasUuids;
    protected $table = 'pengajuans';
    protected $fillable = [
        'user_id',
        'tanggal',
        'keterangan',
        'catatan_mentor',
        'status'
    ];
}
