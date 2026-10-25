<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peringatan extends Model
{
    protected $table = 'peringatan';

    public $timestamps = false;

    protected $fillable = [
        'mahasiswa_id',
        'aturan_id',
        'semester_id',
        'tingkat',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'tingkat' => 'integer',
            'tanggal' => 'date:Y-m-d',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function aturan(): BelongsTo
    {
        return $this->belongsTo(AturanPrestasi::class, 'aturan_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(SemesterAkademik::class, 'semester_id');
    }
}
