<?php

namespace App\Http\Services;

use App\Models\Produto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;
use SimpleXMLElement;

class ProdutoService
{
    protected $totalICMSGeral = 0;

    public function createOrUpdadte(Request $request)
    {
        $validatedData = $request->validate([
            'codigoERP' => 'required|string',
            'descricao' => 'required|string',
            'codigoBarra' => 'required|string',
            'fornecedor' => 'required|string',
            'situacao' => 'required|string',
            'ncm' => 'required|string',
            'cest' => 'required|string',
            'tributacao' => 'required|exists:tributacoes,id',
        ]);

        try {
            $produto = Produto::updateOrCreate(
                ['codigo_erp' => $validatedData['codigoERP']],
                [
                    'descricao' => $validatedData['descricao'],
                    'codigo_barras' => $validatedData['codigoBarra'],
                    'fornecedor' => $validatedData['fornecedor'],
                    'situacao' => $validatedData['situacao'],
                    'ncm' => $validatedData['ncm'],
                    'cest' => $validatedData['cest'],
                    'tributacao_id' => $validatedData['tributacao'],
                    'preco' => 0,
                ]
            );
            return response()->json($produto);
        } catch (Exception $e) {
            throw new \Exception('Erro ao importar planilha.');
        }

    }

    public function importarXML(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|extensions:xml|max:2048',
        ]);

        $file = $request->file('xml_file');

        $desconto = $request->desconto ?? 0;
        $DescontoPorcentagem = $desconto > 0 ? ($desconto / 100) : 0;
        $bonificacaoPorcentagem = ($request->bonificação ?? 0) / 100;

        $xmlContent = file_get_contents($file->getRealPath());

        $xml = new SimpleXMLElement($xmlContent);

        $dataEmissao = (string)$xml->NFe->infNFe->ide->dhEmi;
        $chaveAcesso = (string)$xml->NFe->infNFe['Id'] ?? 'Sem chave';
        $numeroNota = (string)$xml->NFe->infNFe->ide->nNF;
        $totalNF = (float)$xml->NFe->infNFe->total->ICMSTot->vNF;
        $totalBonificacao = $totalNF * $bonificacaoPorcentagem;
        $valorDesconto = $totalNF * $DescontoPorcentagem;
        $emitente = (string)$xml->NFe->infNFe->emit->xNome ?? 'Sem emitente';
        $this->totalICMSGeral = 0;
        $dataEmissaoCarbon = Carbon::parse($dataEmissao)->toDateString();
        $dataEmissaoCarbon = Carbon::createFromFormat('Y-m-d', $dataEmissaoCarbon);
        $duplicatasInfo = [];

        foreach ($xml->NFe->infNFe->cobr->dup as $duplicata) {

            $dataVencimento = (string)$duplicata->dVenc;

            if (strpos($dataVencimento, '/') !== false) {
                $dataVencimentoCarbon = Carbon::createFromFormat('d/m/Y', $dataVencimento);
            } else {
                $dataVencimentoCarbon = Carbon::createFromFormat('Y-m-d', $dataVencimento);
            }

            $diasAteVencimento = $dataEmissaoCarbon->diffInDays($dataVencimentoCarbon, false);

            $duplicatasInfo[] = [
                'numero_duplicata' => (string)$duplicata->nDup,
                'data_vencimento' => $dataVencimento,
                'dias_ate_vencimento' => $diasAteVencimento,
            ];
        }

        $produtos = $this->processarProdutos($xml->NFe->infNFe->det ?? [], $desconto);

        return [
            'emitente' => $emitente,
            'produtos' => $produtos,
            'numero_nota' => $numeroNota,
            'total_nf' => number_format($totalNF, 2, ',', '.'),
            'total_geral_icms' => number_format($this->totalICMSGeral, 2, ',', '.'),
            'total_bonificacao' => number_format($totalBonificacao, 2, ',', '.'),
            'total_desconto' => number_format($valorDesconto, 2, ',', '.'),
            'duplicatas' => $duplicatasInfo
        ];

    }

    private function processarProdutos($itens, $desconto)
    {
        $produtos = [];

        if (isset($itens['prod'])) {

            $itens = [$itens];
        }

        foreach ($itens as $item) {

            $produtos[] = $this->processarProduto($item ?? [], $desconto);
        }

        return $produtos;
    }

    private function processarProduto($produto, $desconto)
    {

        if ((string)$produto->cEAN === 'SEM GTIN') {
            $produtoModel = Produto::where('codigo_fornecedor', (string)$produto->prod->cProd)
                ->with('tributacao')->first();
        } else {
            $produtoModel = Produto::where('codigo_barras', (string)$produto->prod->cEAN ?? '')
                ->with('tributacao')->first();
        }

        $dados = [
            'valor_unitario' => (string)$produto->prod->vUnCom ?? '0',
            'quantidade' => (string)$produto->prod->qCom ?? '0',
            'ipi' => isset($produto->imposto->IPI->IPITrib->vIPI) ? (string)$produto->imposto->IPI->IPITrib->vIPI : '0',

            'mva' => $produtoModel->tributacao->MVA ?? '0',
            'icms' => $produtoModel->tributacao->ICMS ?? '0',
            'icms_st' => $produtoModel->tributacao->ICMS_ST ?? '0',
            'desconto' => $desconto,
        ];

        $resultadoTributacao = TributacaoCalculator::calcular($dados);

        $this->totalICMSGeral = bcadd($this->totalICMSGeral, $resultadoTributacao['total_icms'], 8);

        return [
            'codigo' => (string)$produto->prod->cProd ?? 'Sem código',
            'descricao' => (string)$produto->prod->xProd ?? 'Sem descrição',
            'codigo_barras' => (string)$produto->prod->cEAN ?? 'SEM GTIN',
            'ncm' => (string)$produto->prod->NCM ?? 'Sem NCM',
            'quantidade' => (float)$resultadoTributacao['quantidade'],
            'valor_unitario' => number_format((float)$resultadoTributacao['valor_unitario'], 2, ',', '.'),
            'valor_total' => number_format((float)$produto->prod->vProd ?? 0, 2, ',', '.'),
            'total_a_Pagar' => number_format((float)$resultadoTributacao['valor_devido_destino'], 2, ',', '.'),
            'preco_finalDesconto' => number_format((float)$resultadoTributacao['valor_produto_com_icms_desconto'], 2, ',', '.'),
            'preco_final' => number_format((float)$resultadoTributacao['valor_produto_com_icms'], 2, ',', '.'),
            'total_ICMS' => number_format((float)$resultadoTributacao['total_icms'], 2, ',', '.'),
        ];
    }

}
