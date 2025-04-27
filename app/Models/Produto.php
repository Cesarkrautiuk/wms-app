<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = [
        'descricao', 'fornecedor', 'situacao', 'ncm', 'cest',
        'codigo_barras', 'codigo_erp', 'codigo_fornecedor', 'preco', 'tributacao_id', 'estoque'
    ];
    public function tributacao()
    {
        return $this->belongsTo(Tributacao::class, 'tributacao_id', 'id');

    }
}
