<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AturanPrestasi extends Model
{
    protected $table = 'aturan_prestasi';

    public $timestamps = false;

    protected $fillable = ['ipk_minimal', 'berlaku_mulai'];

    protected function casts(): array
    {
        return [
            'ipk_minimal' => 'decimal:2',
            'berlaku_mulai' => 'date:Y-m-d',
        ];
    }

    public function peringatan(): HasMany
    {
        return $this->hasMany(Peringatan::class, 'aturan_id');
    }
}
