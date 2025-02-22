<?php

use App\Http\Controllers\notaFiscal;
use Illuminate\Support\Facades\Route;


Route::get('/ler-xml',[notaFiscal::class,'index'])->name('notaFiscal.index');
Route::post('/ler-xml', [notaFiscal::class, 'importarXML'])->name('importarXML');
