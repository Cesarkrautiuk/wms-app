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
        bcscale(8); // Define a precisão para 8 casas decimais

        $DescontoPorcentagem = bcdiv($desconto, '100', 8);
        $IPI = '0';

        if ((string)$produto->cEAN === 'SEM GTIN') {
            $produtoModel = Produto::where('codigo_fornecedor', (string)$produto->prod->cProd)
                ->with('tributacao')->first();
        } else {
            $produtoModel = Produto::where('codigo_barras', (string)$produto->prod->cEAN ?? '')
                ->with('tributacao')->first();
        }

        if ($produtoModel && $produtoModel->tributacao) {
            $MVA = bcadd('1', bcdiv((string)$produtoModel->tributacao->MVA, '100', 8), 8);
            $ICMS = bcdiv((string)$produtoModel->tributacao->ICMS, '100', 8);
            $ICMS_ST = bcdiv((string)$produtoModel->tributacao->ICMS_ST, '100', 8);
        } else {
            $MVA = $ICMS = $ICMS_ST = '0';
        }

        $valorUnitario = $this->bcround((string)($produto->prod->vUnCom ?? '0'), 2);
        $quantidade = $this->bcround((string)($produto->prod->qCom ?? '0'), 2);

        if (isset($produto->imposto->IPI->IPITrib->vIPI) && bccomp((string)$produto->imposto->IPI->IPITrib->vIPI, '0') > 0) {
            $valorIPI = (string)$produto->imposto->IPI->IPITrib->vIPI;
            $IPI = bcdiv($valorIPI, $quantidade, 8);
        }

        $ValorComMVA = $this->bcround(bcmul(bcadd($valorUnitario, $IPI, 8), $MVA, 8), 2);
        $valorpagoOrigem = $this->bcround(bcmul($valorUnitario, $ICMS, 8), 2);
        $ValorDevidoDestino = bcmul($ValorComMVA, $ICMS_ST, 8);
        $ValorDescontadoICMSOrigem = $this->bcround(bcsub($ValorDevidoDestino, $valorpagoOrigem, 8), 3);
        $ValorProdutoComICMS = $this->bcround(bcadd($valorUnitario, $ValorDescontadoICMSOrigem, 8), 2);

        $ValorProdutoComICMSDesconto = $ValorProdutoComICMS;
        if (bccomp($DescontoPorcentagem, '0') > 0) {
            $ValorProdutoComICMSDesconto = $this->bcround(bcmul($ValorProdutoComICMS, bcsub('1', $DescontoPorcentagem, 8), 8), 2);
        }

        $totalICMS = $this->bcround(bcmul($quantidade, $ValorDescontadoICMSOrigem, 8), 2);
        $totalDeICMSPagar = number_format((float)$totalICMS, 2, ',', '.');
        $this->totalICMSGeral = bcadd($this->totalICMSGeral, $totalICMS, 8);

        return [
            'codigo' => (string)$produto->prod->cProd ?? 'Sem código',
            'descricao' => (string)$produto->prod->xProd ?? 'Sem descrição',
            'codigo_barras' => (string)$produto->prod->cEAN ?? 'SEM GTIN',
            'ncm' => (string)$produto->prod->NCM ?? 'Sem NCM',
            'quantidade' => (float)$quantidade,
            'valor_unitario' => number_format((float)$valorUnitario, 2, ',', '.'),
            'valor_total' => number_format((float)$produto->prod->vProd ?? 0, 2, ',', '.'),
            'total_a_Pagar' => number_format((float)$ValorDevidoDestino, 2, ',', '.'),
            'preco_finalDesconto' => number_format((float)$ValorProdutoComICMSDesconto, 2, ',', '.'),
            'preco_final' => number_format((float)$ValorProdutoComICMS, 2, ',', '.'),
            'total_ICMS' => $totalDeICMSPagar,
        ];
    }

    private function bcround($number, $precision = 2)
    {
        $factor = bcpow('10', (string)$precision, 8);
        return bcdiv(bcmul($number, $factor, 8), $factor, $precision);
    }
}
