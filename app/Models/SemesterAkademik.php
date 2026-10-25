<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SemesterAkademik extends Model
{
    protected $table = 'semester_akademik';

    public $timestamps = false;

    protected $fillable = [
        'mahasiswa_id',
        'semester_ke',
        'ips',
        'ipk',
        'sks',
        'file_khs',
        'diinput_oleh',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'semester_ke' => 'integer',
            'ips' => 'decimal:2',
            'ipk' => 'decimal:2',
            'sks' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function diinputOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }

    public function peringatan(): HasOne
    {
        return $this->hasOne(Peringatan::class, 'semester_id');
    }
}
