<?php

use App\Http\Controllers\ExcelController;
use App\Http\Controllers\notaFiscal;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\tributacao;
use Illuminate\Support\Facades\Route;


Route::get('/ler-xml',[notaFiscal::class,'index'])->name('notaFiscal.index');
Route::post('/ler-xml', [notaFiscal::class, 'importarXML'])->name('importarXML');
Route::post('/salvar-xml', [notaFiscal::class, 'salvarXML'])->name('salvarXML');
Route::post('/import', [ExcelController::class, 'import'])->name('import');
Route::get('/import', [ExcelController::class, 'index'])->name('import.index');
Route::get('/produto', [ProdutoController::class, 'index'])->name('produto.index');
Route::get('/produto/listar', [ProdutoController::class, 'listarProdutos'])->name('produto.listar');
Route::get('/produto/gerarPdf', [ProdutoController::class, 'gerarPdf'])->name('produto.gerarPdf');
Route::get('/buscar-produto/{id}', [ProdutoController::class, 'buscarProduto'])->name('produto.buscar');
Route::post('/buscar-produto', [ProdutoController::class, 'store'])->name('produto.salvar');
Route::get('/tributacao', [tributacao::class, 'index'])->name('tributacao');
