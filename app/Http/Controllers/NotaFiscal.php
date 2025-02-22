<?php

namespace App\Http\Controllers;

use App\Http\Services\ProdutoService;
use App\Models\Produto;
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

    public function importarXMLddd(Request $request)
    {
        // Validação do arquivo
        $request->validate([
            'xml_file' => 'required|file|mimes:xml|max:2048', // Máximo de 2MB
        ]);

        // Obtém o arquivo enviado
        $file = $request->file('xml_file');

        // Lê o conteúdo do XML
        $xmlContent = file_get_contents($file->getRealPath());

        // Converte XML para um objeto SimpleXMLElement
        $xml = new SimpleXMLElement($xmlContent);

        // Converte para JSON e depois para Array (opcional)
        $json = json_encode($xml);
        $array = json_decode($json, true);

        $chaveAcesso = $array['NFe']['infNFe']['@attributes']['Id'] ?? 'Sem chave';
        $numeroNota = (string)$xml->NFe->infNFe->ide->nNF;  // Número da nota
        $totalNF = (float)$xml->NFe->infNFe->total->ICMSTot->vNF;

        $emitente = $array['NFe']['infNFe']['emit']['xNome'] ?? 'Sem emitente';

        $produtos = [];

        if (isset($array['NFe']['infNFe']['det'])) {
            $itens = $array['NFe']['infNFe']['det'];

            // Se houver apenas um produto, ele pode não estar em um array, então forçamos a conversão
            if (isset($itens['prod'])) {
                $itens = [$itens];
            }

            foreach ($itens as $item) {
                $DescontoPorcetagem = 15 / 100;

                $produto = $item['prod'] ?? [];
                $produtoModel = Produto::where('codigo_barras', $produto['cEAN'] ?? '')->with('tributacao')->first();
                if ($produtoModel && $produtoModel->tributacao) {
                    $MVA = 1 + ($produtoModel->tributacao->MVA / 100);
                    $ICMS = $produtoModel->tributacao->ICMS / 100;
                    $ICMS_ST = $produtoModel->tributacao->ICMS_ST / 100;
                }
                $valorUnitario = round((float)($produto['vUnCom'] ?? 0), 2);
                $quantidade = round((float)($produto['qCom'] ?? 0), 2);

                // Cálculos fiscais
                $ValorComMVA = $valorUnitario * $MVA;
                $valorpagoOrigem = $valorUnitario * $ICMS;
                $ValorDevidoDestino = $ValorComMVA * $ICMS_ST;
                $ValorDescontadoICMSOrigem = $ValorDevidoDestino - $valorpagoOrigem;

                $ValorProdutoComICMS = round($valorUnitario + $ValorDescontadoICMSOrigem, 2);

                // Aplicação do desconto
                $ValorProdutoComICMSDesconto = round($ValorProdutoComICMS * (1 - $DescontoPorcetagem), 2);

                // Total de ICMS
                $totalICMS = $quantidade * $ValorDescontadoICMSOrigem;
                $totalDeICMSPagar = number_format($totalICMS, 2, ',', '.');

                $produtos[] = [
                    'codigo' => $produto['cProd'] ?? 'Sem código',
                    'descricao' => $produto['xProd'] ?? 'Sem descrição',
                    'codigo_barras' => $produto['cEAN'] ?? 'SEM GTIN',
                    'ncm' => $produto['NCM'] ?? 'Sem NCM',
                    'quantidade' => $produto['qCom'] ?? 0,
                    'valor_unitario' => round((float)($produto['vUnCom'] ?? 0), 2),
                    'valor_total' => $produto['vProd'] ?? 0,
                    'total ICMS' => number_format($valorpagoOrigem, 2, ',', '.'),
                    'total_a_Pagar' => number_format($ValorDevidoDestino, 2, ',', '.'),
                    'preco_finalDesconto' => $ValorProdutoComICMSDesconto,
                    'preco_final' => number_format($ValorProdutoComICMS, 2, ',', '.'),
                    'total_ICMS' => $totalDeICMSPagar,
                ];
            }
        }
        return response()->json([
            'emitente' => $emitente,
            'produtos' => $produtos,
            'numero_nota' => $numeroNota,
            'total_nf' => number_format($totalNF, 2, ',', '.')
        ]);

    }

    public function importarXML(Request $request)
    {
        // Validação do arquivo
        $request->validate([
            'xml_file' => 'required|file|mimes:xml|max:2048',
        ]);

        // Processa o XML usando o Service
        $dados = $this->produtoService->importarXML($request->file('xml_file'));

        return response()->json($dados);
    }
}
