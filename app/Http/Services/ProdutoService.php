<?php

namespace App\Http\Services;

use App\Models\Produto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use SimpleXMLElement;

class ProdutoService
{
    protected $totalICMSGeral = 0;

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

        $dataEmissao = (string)$xml->NFe->infNFe->ide->dEmi;
        $chaveAcesso = (string)$xml->NFe->infNFe['Id'] ?? 'Sem chave';
        $numeroNota = (string)$xml->NFe->infNFe->ide->nNF;
        $totalNF = (float)$xml->NFe->infNFe->total->ICMSTot->vNF;
        $totalBonificacao = $totalNF * $bonificacaoPorcentagem;
        $valorDesconto = $totalNF * $DescontoPorcentagem;
        $emitente = (string)$xml->NFe->infNFe->emit->xNome ?? 'Sem emitente';
        $this->totalICMSGeral = 0;
        $dataEmissaoCarbon = Carbon::parse($dataEmissao);
        $dataEmissaoSomente = $dataEmissaoCarbon->format('Y-m-d');
        $dataEmissaoCarbon = Carbon::createFromFormat('Y-m-d', $dataEmissaoSomente);
        $duplicatasInfo = [];

        foreach ($xml->NFe->infNFe->cobr->dup as $duplicata) {
            $dataVencimento = (string)$duplicata->dVenc;
            $dataVencimentoCarbon = Carbon::createFromFormat('Y-m-d', $dataVencimento);
            $diasAteVencimento = $dataEmissaoCarbon->diffInDays($dataVencimentoCarbon);

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

            $produtos[] = $this->processarProduto($item->prod ?? [], $desconto);
        }

        return $produtos;
    }

    private function processarProduto($produto, $desconto)
    {
        $DescontoPorcentagem = $desconto > 0 ? ($desconto / 100) : 0;

        if ((string)$produto->cEAN == 'SEM GTIN') {
            $produtoModel = Produto::where('codigo_fornecedor', (string)$produto->cProd)->with('tributacao')->first();
        } else {
            $produtoModel = Produto::where('codigo_barras', (string)$produto->cEAN ?? '')->with('tributacao')->first();
        }


        if ($produtoModel && $produtoModel->tributacao) {
            $MVA = 1 + ($produtoModel->tributacao->MVA / 100);
            $ICMS = $produtoModel->tributacao->ICMS / 100;
            $ICMS_ST = $produtoModel->tributacao->ICMS_ST / 100;
        } else {
            $MVA = $ICMS = $ICMS_ST = 0;
        }

        $valorUnitario = round((float)($produto->vUnCom ?? 0), 2);
        $quantidade = round((float)($produto->qCom ?? 0), 2);

        $ValorComMVA = $valorUnitario * $MVA;
        $valorpagoOrigem = $valorUnitario * $ICMS;
        $ValorDevidoDestino = $ValorComMVA * $ICMS_ST;
        $ValorDescontadoICMSOrigem = $ValorDevidoDestino - $valorpagoOrigem;

        $ValorProdutoComICMS = round($valorUnitario + $ValorDescontadoICMSOrigem, 2);
        $ValorProdutoComICMSDesconto = $ValorProdutoComICMS;

        if ($DescontoPorcentagem > 0) {
            $ValorProdutoComICMSDesconto = round($ValorProdutoComICMS * (1 - $DescontoPorcentagem), 2);
        }

        $totalICMS = $quantidade * $ValorDescontadoICMSOrigem;
        $totalDeICMSPagar = number_format($totalICMS, 2, ',', '.');
        $this->totalICMSGeral += $totalICMS;

        return [
            'codigo' => (string)$produto->cProd ?? 'Sem código',
            'descricao' => (string)$produto->xProd ?? 'Sem descrição',
            'codigo_barras' => (string)$produto->cEAN ?? 'SEM GTIN',
            'ncm' => (string)$produto->NCM ?? 'Sem NCM',
            'quantidade' => (float)$produto->qCom ?? 0,
            'valor_unitario' => number_format(round((float)($produto->vUnCom ?? 0), 2), 2, ',', '.'),
            'valor_total' => number_format((float)$produto->vProd ?? 0, 2, ',', '.'),
            'total_a_Pagar' => number_format($ValorDevidoDestino, 2, ',', '.'),
            'preco_finalDesconto' => number_format($ValorProdutoComICMSDesconto, 2, ',', '.'),
            'preco_final' => number_format($ValorProdutoComICMS, 2, ',', '.'),
            'total_ICMS' => $totalDeICMSPagar,
        ];
    }
}
