<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use HasUuids;
    protected $table = 'activity_logs';
    protected $fillable = [
        'user_id',
        'nama',
        'action',
        'model',
        'model_id',
        'changes',
        'ip_address',
        'user_agent'
    ];

}
