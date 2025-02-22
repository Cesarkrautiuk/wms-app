<?php

namespace App\Http\Services;

use App\Models\Produto;
use SimpleXMLElement;

class ProdutoService
{
    protected $totalICMSGeral = 0;

    public function importarXML($file)
    {
        // Lê o conteúdo do XML
        $xmlContent = file_get_contents($file->getRealPath());

        // Converte XML para um objeto SimpleXMLElement
        $xml = new SimpleXMLElement($xmlContent);

        // Converte para JSON e depois para Array (opcional)
        $json = json_encode($xml);
        $array = json_decode($json, true);

        $chaveAcesso = $array['NFe']['infNFe']['@attributes']['Id'] ?? 'Sem chave';
        $numeroNota = (string)$xml->NFe->infNFe->ide->nNF;
        $totalNF = (float)$xml->NFe->infNFe->total->ICMSTot->vNF;

        $emitente = $array['NFe']['infNFe']['emit']['xNome'] ?? 'Sem emitente';

        $produtos = $this->processarProdutos($array['NFe']['infNFe']['det'] ?? []);

        return [
            'emitente' => $emitente,
            'produtos' => $produtos,
            'numero_nota' => $numeroNota,
            'total_nf' => number_format($totalNF, 2, ',', '.'),
            'total_geral_icms' => number_format($this->totalICMSGeral, 2, ',', '.'),
        ];
    }

    private function processarProdutos($itens)
    {
        $produtos = [];

        // Se houver apenas um produto, convertemos para array
        if (isset($itens['prod'])) {
            $itens = [$itens];
        }

        foreach ($itens as $item) {
            $produtos[] = $this->processarProduto($item['prod'] ?? []);
        }

        return $produtos;
    }

    private function processarProduto($produto)
    {
        $DescontoPorcentagem = 15 / 100;

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
        $ValorProdutoComICMSDesconto = round($ValorProdutoComICMS * (1 - $DescontoPorcentagem), 2);

        // Total de ICMS
        $totalICMS = $quantidade * $ValorDescontadoICMSOrigem;
        $totalDeICMSPagar = number_format($totalICMS, 2, ',', '.');
        $this->totalICMSGeral += $totalICMS;
        return [
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
