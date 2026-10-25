<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumentasiMingguan extends Model
{
    protected $table = 'dokumentasi_mingguan';

    public $timestamps = false;

    protected $fillable = [
        'mahasiswa_id',
        'tahun',
        'minggu_ke',
        'file_foto',
        'keterangan',
        'status_verifikasi',
        'catatan_admin',
        'diverifikasi_oleh',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'minggu_ke' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
