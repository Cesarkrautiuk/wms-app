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
    public function salvarXML(Request $request)
    {
        // Validar e processar os dados enviados (verifique a estrutura que está sendo recebida)
        $data = $request->all();
        // Exemplo de salvamento no banco de dados ou qualquer outra lógica
        try {
            // Lógica para salvar os dados, como salvar produtos ou notas fiscais
            // Isso depende da estrutura do seu responseData
            // Produto::create($data);

            return response()->json(['message' => 'Dados salvos com sucesso!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar os dados: ' . $e->getMessage()], 500);
        }
    }
}
