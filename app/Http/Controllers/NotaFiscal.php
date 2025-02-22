<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleXMLElement;

class notaFiscal extends Controller
{
    public function index()
    {
        return view('importarXML');
    }

    public function importarXML(Request $request)
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
                $produto = $item['prod'] ?? [];
                $produtos[] = [
                    'codigo' => $produto['cProd'] ?? 'Sem código',
                    'descricao' => $produto['xProd'] ?? 'Sem descrição',
                    'codigo_barras' => $produto['cEAN'] ?? 'SEM GTIN',
                    'ncm' => $produto['NCM'] ?? 'Sem NCM',
                    'quantidade' => $produto['qCom'] ?? 0,
                    'valor_unitario' => round((float) ($produto['vUnCom'] ?? 0), 2),
                    'valor_total' => $produto['vProd'] ?? 0,
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
}
