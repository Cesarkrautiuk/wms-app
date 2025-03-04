<?php

namespace App\Http\Controllers;

use App\Http\Services\ProdutoService;
use App\Models\Produto;
use App\Models\Tributacao;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    protected $produtoService;

    public function __construct(ProdutoService $ProdutoService)
    {
        $this->produtoService = $ProdutoService;
    }

    public function index()
    {
        $tributacao = Tributacao::all();
        return view('produto', compact('tributacao'));
    }

    public function buscarProduto($idERP)
    {
        $produto = Produto::where('codigo_erp', $idERP)->with('tributacao')->first();

        if ($produto) {
            return response()->json([
                'codigo' => $produto->id,
                'descricao' => $produto->descricao,
                'codigoBarras' => $produto->codigo_barras,
                'situacao' => $produto->situacao,
                'fornecedor' => $produto->fornecedor,
                'tributacao' => $produto->tributacao->id,
                'ncm' => $produto->ncm,
                'cest' => $produto->cest,
            ]);
        }

    }

    public function store(Request $request)
    {
        try {
            $this->produtoService->createOrUpdadte($request);
            return redirect()->route('produto.index')->with('success', "Produto gravado com sucesso!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
