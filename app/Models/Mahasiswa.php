<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    public const STATUS_KIPK = ['aktif', 'cuti', 'lulus', 'dicabut', 'mengundurkan_diri'];

    protected $table = 'mahasiswa';

    protected $fillable = [
        'user_id',
        'nim',
        'nama',
        'prodi',
        'angkatan',
        'status_kipk',
    ];

    protected function casts(): array
    {
        return ['angkatan' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function semester(): HasMany
    {
        return $this->hasMany(SemesterAkademik::class);
    }

    public function peringatan(): HasMany
    {
        return $this->hasMany(Peringatan::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function dokumentasiMingguan(): HasMany
    {
        return $this->hasMany(DokumentasiMingguan::class, 'mahasiswa_id');
    }
}
