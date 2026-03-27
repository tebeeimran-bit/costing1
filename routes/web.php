<?php

use App\Http\Controllers\CostingController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\DocumentReceiptController;
use App\Http\Controllers\TrackingDocumentController;
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
Route::post('/database/customers', [DatabaseController::class, 'storeCustomer'])->name('database.customers.store');
Route::put('/database/customers/{id}', [DatabaseController::class, 'updateCustomer'])->name('database.customers.update');
Route::delete('/database/customers/{id}', [DatabaseController::class, 'destroyCustomer'])->name('database.customers.destroy');
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

Route::get('/tracking-documents', [TrackingDocumentController::class, 'index'])->name('tracking-documents.index');
Route::get('/tracking-documents/new', [TrackingDocumentController::class, 'create'])->name('tracking-documents.create');
Route::post('/tracking-documents/receipt', [TrackingDocumentController::class, 'storeReceipt'])->name('tracking-documents.store-receipt');
Route::post('/tracking-documents/{revision}/mark-cogm', [TrackingDocumentController::class, 'markCogmGenerated'])->name('tracking-documents.mark-cogm');
Route::post('/tracking-documents/{revision}/submit-cogm', [TrackingDocumentController::class, 'submitCogm'])->name('tracking-documents.submit-cogm');
Route::post('/tracking-documents/{revision}/update-files', [TrackingDocumentController::class, 'updateFiles'])->name('tracking-documents.update-files');
Route::post('/tracking-documents/{project}/update-project-info', [TrackingDocumentController::class, 'updateProjectInfo'])->name('tracking-documents.update-project-info');
Route::delete('/tracking-documents/{project}', [TrackingDocumentController::class, 'destroyProject'])->name('tracking-documents.destroy-project');
Route::post('/tracking-documents/{revision}/unpriced-price', [TrackingDocumentController::class, 'updateUnpricedPartPrice'])->name('tracking-documents.update-unpriced-price');
Route::get('/tracking-documents/{revision}/{type}', [TrackingDocumentController::class, 'download'])
	->where('type', 'partlist|umh|a00|a04|a05')
	->name('tracking-documents.download');
Route::get('/tracking-documents/{revision}/export-unpriced/{format}', [TrackingDocumentController::class, 'exportUnpricedParts'])
	->where('format', 'excel|pdf')
	->name('tracking-documents.export-unpriced');
