<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alamat extends Model
{
    use HasFactory;

    protected $table = 'alamats';
    protected $fillable = ['user_id', 'label', 'nama_penerima', 'no_hp', 'alamat_pengiriman', 'utama'];
    protected function casts(): array
    {
        return ['utama' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
