<?php

use App\Http\Controllers\ExcelController;
use App\Http\Controllers\notaFiscal;
use Illuminate\Support\Facades\Route;


Route::get('/ler-xml',[notaFiscal::class,'index'])->name('notaFiscal.index');
Route::post('/ler-xml', [notaFiscal::class, 'importarXML'])->name('importarXML');
Route::post('/import', [ExcelController::class, 'import'])->name('import');
Route::get('/import', [ExcelController::class, 'index'])->name('import.index');
