<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index()
    {
        return view('produto');
    }

    public function buscarProduto($idERP)
    {
        // Busca o produto no banco de dados pelo código ERP
        $produto = Produto::where('codigo_erp', $idERP) ->with('tributacao')->first();
        // Se o produto for encontrado, retorna os dados em formato JSON
        if ($produto) {
            return response()->json([
                'codigo' => $produto->id,
                'descricao' => $produto->descricao,
                'codigoBarras' => $produto->codigo_barras,
                'situacao' => $produto->situacao,
                'fornecedor' => $produto->fornecedor,
                'tributacao' => $produto->tributacao->descricao
            ]);
        }

    }
}
