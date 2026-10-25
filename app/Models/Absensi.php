<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    public const STATUS = ['hadir', 'izin', 'alpa'];

    protected $table = 'absensi';

    public $timestamps = false;

    protected $fillable = ['kegiatan_id', 'mahasiswa_id', 'waktu', 'status', 'file_foto'];

    protected function casts(): array
    {
        return ['waktu' => 'datetime'];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(KegiatanPembinaan::class, 'kegiatan_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
