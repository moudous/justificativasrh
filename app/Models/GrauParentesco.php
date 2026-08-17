<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrauParentesco extends Model
{
    use SoftDeletes;

    protected $table = 'graus_parentescos';

    protected $fillable = ['nome', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public function justificativas(): HasMany
    {
        return $this->hasMany(Justificativa::class);
    }
}
