<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidade extends Model
{
    use SoftDeletes;

    protected $fillable = ['nome', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public function setores(): HasMany
    {
        return $this->hasMany(Setor::class);
    }
}
