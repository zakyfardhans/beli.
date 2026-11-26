<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $fillable = [
        'user_id',
        'aksi',
        'entitas',
        'entitas_id',
        'nilai_lama',
        'nilai_baru',
        'ip',
    ];
}
