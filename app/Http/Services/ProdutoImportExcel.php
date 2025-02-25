<?php

namespace App\Http\Services;

use App\Models\Produto;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ProdutoImportExcel
{
    public function readExcel($file)
    {
        try {

            $reader = IOFactory::createReaderForFile($file);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());

            $expectedColumns = 7;
            if ($highestColumnIndex < $expectedColumns) {
                throw new \Exception("O arquivo Excel não tem o formato esperado. Verifique se todas as colunas estão presentes.");
            }

            $countInserted = 0;
            $countUpdated = 0;

            for ($row = 2; $row <= $highestRow; $row++) {
                $codigoERP = trim((string)$sheet->getCell(Coordinate::stringFromColumnIndex(1) . $row)->getValue());
                $name = trim((string)$sheet->getCell(Coordinate::stringFromColumnIndex(2) . $row)->getValue());
                $fornecedor = trim((string)$sheet->getCell(Coordinate::stringFromColumnIndex(3) . $row)->getValue());
                $ncm = trim((string)$sheet->getCell(Coordinate::stringFromColumnIndex(4) . $row)->getValue());
                $cest = trim((string)$sheet->getCell(Coordinate::stringFromColumnIndex(5) . $row)->getValue());
                $codBarra = trim((string)$sheet->getCell(Coordinate::stringFromColumnIndex(6) . $row)->getValue());
                $codigoFornecedor = trim((string)$sheet->getCell(Coordinate::stringFromColumnIndex(7) . $row)->getValue());

                $product = Produto::where('codigo_erp', $codigoERP)->first();

                $updateData = [
                    'descricao' => $name,
                    'fornecedor' => $fornecedor,
                    'ncm' => $ncm,
                    'cest' => $cest,
                    'codigo_barras' => $codBarra,
                    'codigo_fornecedor' => $codigoFornecedor,
                    'tributacao_id' => 1,
                    'preco' => 0,
                ];

                if ($product) {
                    if (array_diff_assoc($updateData, $product->toArray())) {
                        $product->update($updateData);
                        $countUpdated++;
                    }
                } else {
                    Produto::create(array_merge(['codigo_erp' => $codigoERP], $updateData));
                    $countInserted++;
                }

                if ($row % 100 === 0) {
                    gc_collect_cycles();
                }
            }

            unset($spreadsheet);
            gc_collect_cycles();

            return [
                'countInserted' => $countInserted,
                'countUpdated' => $countUpdated
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao processar arquivo Excel: ' . $e->getMessage());
            throw new \Exception('Erro ao importar planilha.');
        }
    }

    // Método para escrever dados em um arquivo Excel
    public function writeExcel($data, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Escrever os dados na planilha
        foreach ($data as $rowIndex => $rowData) {
            foreach ($rowData as $columnIndex => $value) {
                $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 1, $value);
            }
        }

        // Escrever o arquivo Excel
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
    }
}
