<?php

use App\Http\Controllers\CostingController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\DocumentReceiptController;
use App\Http\Controllers\TrackingDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CostingController::class, 'dashboard'])->name('dashboard');

// Test endpoint for debugging Codespaces access
Route::get('/test', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now(),
        'url' => request()->fullUrl(),
        'host' => request()->host(),
        'ip' => request()->ip(),
        'method' => request()->method(),
        'path' => request()->path(),
        'headers' => [
            'user-agent' => request()->header('user-agent'),
            'x-forwarded-for' => request()->header('x-forwarded-for'),
            'x-forwarded-host' => request()->header('x-forwarded-host'),
            'x-forwarded-proto' => request()->header('x-forwarded-proto'),
        ]
    ]);
});

Route::get('/database', [DatabaseController::class, 'index'])->name('database');
Route::get('/database/products', [DatabaseController::class, 'products'])->name('database.products');
Route::get('/database/parts', [DatabaseController::class, 'parts'])->name('database.parts');
Route::get('/database/parts/template', [DatabaseController::class, 'downloadPartsTemplate'])->name('database.parts.template');
Route::post('/database/parts/import', [DatabaseController::class, 'importPartsExcel'])->name('database.parts.import');
Route::delete('/database/parts/bulk-delete', [DatabaseController::class, 'destroyPartsBulk'])->name('database.parts.destroy-bulk');
Route::delete('/database/parts/destroy-all', [DatabaseController::class, 'destroyPartsAll'])->name('database.parts.destroy-all');
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
Route::put('/database/cycle-time-templates/{id}', [DatabaseController::class, 'updateCycleTimeTemplate'])->name('database.cycle-time-templates.update');
Route::delete('/database/cycle-time-templates/{id}', [DatabaseController::class, 'destroyCycleTimeTemplate'])->name('database.cycle-time-templates.destroy');
Route::get('/database/business-categories', [DatabaseController::class, 'businessCategories'])->name('database.business-categories');
Route::post('/database/business-categories', [DatabaseController::class, 'storeBusinessCategory'])->name('database.business-categories.store');
Route::put('/database/business-categories/{id}', [DatabaseController::class, 'updateBusinessCategory'])->name('database.business-categories.update');
Route::delete('/database/business-categories/{id}', [DatabaseController::class, 'destroyBusinessCategory'])->name('database.business-categories.destroy');
Route::get('/database/plants', [DatabaseController::class, 'plants'])->name('database.plants');
Route::post('/database/plants', [DatabaseController::class, 'storePlant'])->name('database.plants.store');
Route::put('/database/plants/{id}', [DatabaseController::class, 'updatePlant'])->name('database.plants.update');
Route::delete('/database/plants/{id}', [DatabaseController::class, 'destroyPlant'])->name('database.plants.destroy');
Route::get('/database/pics', [DatabaseController::class, 'pics'])->name('database.pics');
Route::post('/database/pics', [DatabaseController::class, 'storePic'])->name('database.pics.store');
Route::put('/database/pics/{id}', [DatabaseController::class, 'updatePic'])->name('database.pics.update');
Route::delete('/database/pics/{id}', [DatabaseController::class, 'destroyPic'])->name('database.pics.destroy');
Route::get('/form', [CostingController::class, 'form'])->name('form');
Route::post('/costing/store', [CostingController::class, 'store'])->name('costing.store');
Route::get('/costing/import-partlist', fn () => redirect()->route('form'))->name('costing.import-partlist.get');
Route::post('/costing/import-partlist', [CostingController::class, 'importPartlist'])->name('costing.import-partlist');
Route::get('/document-receipts', [DocumentReceiptController::class, 'index'])->name('document-receipts.index');
Route::post('/document-receipts', [DocumentReceiptController::class, 'store'])->name('document-receipts.store');
Route::get('/document-receipts/{documentReceipt}/{type}', [DocumentReceiptController::class, 'download'])
	->where('type', 'partlist|umh')
	->name('document-receipts.download');

Route::get('/tracking-documents', [TrackingDocumentController::class, 'index'])->name('tracking-documents.index');
Route::get('/tracking-documents/new', [TrackingDocumentController::class, 'create'])->name('tracking-documents.create');
Route::post('/tracking-documents/receipt', [TrackingDocumentController::class, 'storeReceipt'])->name('tracking-documents.store-receipt');
Route::post('/tracking-documents/{revision}/process-form-input', [TrackingDocumentController::class, 'processToFormInput'])->name('tracking-documents.process-form-input');
Route::post('/tracking-documents/{revision}/mark-cogm', [TrackingDocumentController::class, 'markCogmGenerated'])->name('tracking-documents.mark-cogm');
Route::post('/tracking-documents/{revision}/submit-cogm', [TrackingDocumentController::class, 'submitCogm'])->name('tracking-documents.submit-cogm');
Route::post('/tracking-documents/{revision}/update-files', [TrackingDocumentController::class, 'updateFiles'])->name('tracking-documents.update-files');
Route::post('/tracking-documents/{project}/update-project-info', [TrackingDocumentController::class, 'updateProjectInfo'])->name('tracking-documents.update-project-info');
Route::delete('/tracking-documents/{project}', [TrackingDocumentController::class, 'destroyProject'])->name('tracking-documents.destroy-project');
Route::post('/tracking-documents/{revision}/unpriced-price', [TrackingDocumentController::class, 'updateUnpricedPartPrice'])->name('tracking-documents.update-unpriced-price');
Route::post('/tracking-documents/{revision}/unpriced-delete', [TrackingDocumentController::class, 'deleteUnpricedPart'])->name('tracking-documents.delete-unpriced-part');
Route::post('/tracking-documents/{revision}/unpriced-restore', [TrackingDocumentController::class, 'restoreUnpricedPart'])->name('tracking-documents.restore-unpriced-part');
Route::get('/tracking-documents/{revision}/{type}', [TrackingDocumentController::class, 'download'])
	->where('type', 'partlist|umh|a00|a04|a05')
	->name('tracking-documents.download');
Route::get('/tracking-documents/{revision}/export-unpriced/{format}', [TrackingDocumentController::class, 'exportUnpricedParts'])
	->where('format', 'excel|pdf')
	->name('tracking-documents.export-unpriced');
