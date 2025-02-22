<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tributacao extends Model
{
    protected $table = 'tributacoes';

    // Definir as colunas que podem ser atribuídas em massa (mass assignment)
    protected $fillable = [
        'MVA',
        'ICMS',
        'ICMS_ST',
        'descricao'
    ];

    public $timestamps = true;

    public function produtos()
    {
        return $this->hasMany(Produto::class, 'tributacao_id', 'id');
    }
}

