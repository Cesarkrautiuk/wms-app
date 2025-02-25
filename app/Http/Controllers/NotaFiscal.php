<?php

namespace App\Http\Controllers;

use App\Http\Services\ProdutoService;
use Illuminate\Http\Request;
use SimpleXMLElement;

class notaFiscal extends Controller
{
    protected $produtoService;

    public function __construct(ProdutoService $produtoService)
    {
        $this->produtoService = $produtoService;
    }

    public function index()
    {
        return view('importarXML');
    }

    public function importarXML(Request $request)
    {
        $dados = $this->produtoService->importarXML($request);

        return response()->json($dados);
    }
}
