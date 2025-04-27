<?php

namespace App\Http\Services;

class TributacaoCalculator
{
    public static function calcular(array $data): array
    {
        bcscale(8);

        $valorUnitario = self::bcround((string)($data['valor_unitario'] ?? '0'), 2);
        $quantidade = self::bcround((string)($data['quantidade'] ?? '0'), 2);
        $ipiTotal = (string)($data['ipi'] ?? '0');

        $IPI = bccomp($ipiTotal, '0', 8) > 0 && $quantidade > 0
            ? bcdiv($ipiTotal, $quantidade, 8)
            : '0';

        $mva = isset($data['mva']) ? bcadd('1', bcdiv((string)$data['mva'], '100', 8), 8) : '0';
        $icms = isset($data['icms']) ? bcdiv((string)$data['icms'], '100', 8) : '0';
        $icmsSt = isset($data['icms_st']) ? bcdiv((string)$data['icms_st'], '100', 8) : '0';
        $desconto = (string)($data['desconto'] ?? '0');
        $descontoPorcentagem = bcdiv($desconto, '100', 8);

        $valorComMVA = self::bcround(bcmul(bcadd($valorUnitario, $IPI, 8), $mva, 8), 2);
        $valorPagoOrigem = self::bcround(bcmul($valorUnitario, $icms, 8), 2);
        $valorDevidoDestino = bcmul($valorComMVA, $icmsSt, 8);
        $valorDescontadoICMSOrigem = self::bcround(bcsub($valorDevidoDestino, $valorPagoOrigem, 8), 3);
        $valorProdutoComICMS = self::bcround(bcadd($valorUnitario, $valorDescontadoICMSOrigem, 8), 2);

        $valorProdutoComICMSDesconto = $valorProdutoComICMS;
        if (bccomp($descontoPorcentagem, '0') > 0) {
            $valorProdutoComICMSDesconto = self::bcround(bcmul($valorProdutoComICMS, bcsub('1', $descontoPorcentagem, 8), 8), 2);
        }

        $totalICMS = self::bcround(bcmul($quantidade, $valorDescontadoICMSOrigem, 8), 2);

        return [
            'valor_unitario' => $valorUnitario,
            'quantidade' => $quantidade,
            'IPI' => $IPI,
            'valor_com_mva' => $valorComMVA,
            'valor_pago_origem' => $valorPagoOrigem,
            'valor_devido_destino' => $valorDevidoDestino,
            'valor_descontado_icms_origem' => $valorDescontadoICMSOrigem,
            'valor_produto_com_icms' => $valorProdutoComICMS,
            'valor_produto_com_icms_desconto' => $valorProdutoComICMSDesconto,
            'total_icms' => $totalICMS,
        ];

    }

    private static function bcround($number, $precision = 2)
    {
        $factor = bcpow('10', (string)$precision, 8);
        return bcdiv(bcmul($number, $factor, 8), $factor, $precision);
    }


}
