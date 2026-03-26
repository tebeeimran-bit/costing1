<?php

use App\Http\Controllers\CostingController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\DocumentReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CostingController::class, 'dashboard'])->name('dashboard');
Route::get('/database', [DatabaseController::class, 'index'])->name('database');
Route::get('/database/products', [DatabaseController::class, 'products'])->name('database.products');
Route::get('/database/parts', [DatabaseController::class, 'parts'])->name('database.parts');
Route::get('/database/parts/create', [DatabaseController::class, 'createPart'])->name('database.parts.create');
Route::post('/database/parts', [DatabaseController::class, 'storePart'])->name('database.parts.store');
Route::get('/database/parts/{id}/edit', [DatabaseController::class, 'editPart'])->name('database.parts.edit');
Route::put('/database/parts/{id}', [DatabaseController::class, 'updatePart'])->name('database.parts.update');
Route::delete('/database/parts/{id}', [DatabaseController::class, 'destroyPart'])->name('database.parts.destroy');
Route::get('/database/costing', [DatabaseController::class, 'costing'])->name('database.costing');
Route::get('/database/customers', [DatabaseController::class, 'customers'])->name('database.customers');
Route::get('/database/cycle-time-templates', [DatabaseController::class, 'cycleTimeTemplates'])->name('database.cycle-time-templates');
Route::post('/database/cycle-time-templates', [DatabaseController::class, 'storeCycleTimeTemplate'])->name('database.cycle-time-templates.store');
Route::delete('/database/cycle-time-templates/{id}', [DatabaseController::class, 'destroyCycleTimeTemplate'])->name('database.cycle-time-templates.destroy');
Route::get('/form', [CostingController::class, 'form'])->name('form');
Route::post('/costing/store', [CostingController::class, 'store'])->name('costing.store');
Route::get('/document-receipts', [DocumentReceiptController::class, 'index'])->name('document-receipts.index');
Route::post('/document-receipts', [DocumentReceiptController::class, 'store'])->name('document-receipts.store');
Route::get('/document-receipts/{documentReceipt}/{type}', [DocumentReceiptController::class, 'download'])
	->where('type', 'partlist|umh')
	->name('document-receipts.download');
