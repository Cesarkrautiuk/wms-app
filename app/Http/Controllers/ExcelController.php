<?php

namespace App\Http\Controllers;

use App\Http\Services\ProdutoImportExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    protected $produtoService;

    public function __construct(ProdutoImportExcel $excelService)
    {
        $this->produtoService = $excelService;
    }

    public function index(Request $request)
    {
        return view('importProduto');
    }

    public function import(Request $request)
    {
        try {
            $dados = $this->produtoService->readExcel($request->file('excel_file'));
            $countInserted = $dados['countInserted'];
            $countUpdated = $dados['countUpdated'];
            return redirect()->route('import.index')->with('success', "Importação concluída com sucesso! Inseridos: $countInserted, Atualizados: $countUpdated");
        } catch (\Exception $e) {
            Log::error('Erro ao importar planilha: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Método para exportar dados para Excel
    public function export()
    {
        // Exemplo de dados para exportação
        $data = [
            ['Nome', 'Idade', 'Cidade'],
            ['João', 28, 'São Paulo'],
            ['Maria', 34, 'Rio de Janeiro'],
            ['Pedro', 22, 'Belo Horizonte'],
        ];

        // Caminho onde o arquivo será salvo
        $filename = storage_path('app/public/relatorio.xlsx');

        // Escrever os dados no arquivo Excel
        $this->excelService->writeExcel($data, $filename);

        // Forçar o download do arquivo
        return response()->download($filename);
    }
}
