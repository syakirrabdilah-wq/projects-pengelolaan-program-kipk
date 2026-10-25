<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KegiatanPembinaan extends Model
{
    protected $table = 'kegiatan_pembinaan';

    public $timestamps = false;

    protected $fillable = ['judul', 'tanggal', 'absen_buka', 'absen_tutup', 'dibuat_oleh'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
            'absen_buka' => 'datetime',
            'absen_tutup' => 'datetime',
        ];
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'kegiatan_id');
    }
}
