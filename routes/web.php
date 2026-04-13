<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CostingController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\DocumentReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TrackingDocumentController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// All app routes require authentication
Route::middleware('auth')->group(function () {

Route::get('/', [CostingController::class, 'dashboard'])->name('dashboard');
Route::get('/compare-costing', [CostingController::class, 'compare'])->name('compare.costing');
Route::get('/compare-costing/revisions-search', [CostingController::class, 'searchCompareRevisions'])->name('compare.costing.revisions-search');

// Reports & Analysis
Route::get('/resume-cogm', [ReportController::class, 'resumeCogm'])->name('resume-cogm');
Route::get('/analisis-tren', [ReportController::class, 'analisisTren'])->name('analisis-tren');
Route::get('/cogm-submissions', [ReportController::class, 'cogmSubmissions'])->name('cogm-submissions');
Route::get('/laporan', [ReportController::class, 'laporan'])->name('laporan');
Route::get('/audit-trail', [ReportController::class, 'auditTrail'])->name('audit-trail');

// Database - Rate & Kurs
Route::get('/database/rate-kurs', [ReportController::class, 'rateKurs'])->name('rate-kurs');
Route::post('/database/rate-kurs', [ReportController::class, 'storeExchangeRate'])->name('rate-kurs.store');
Route::delete('/database/rate-kurs/{id}', [ReportController::class, 'destroyExchangeRate'])->name('rate-kurs.destroy');

// Database - Product
Route::get('/database/products-manage', [ReportController::class, 'products'])->name('products.index');
Route::post('/database/products-manage', [ReportController::class, 'storeProduct'])->name('products.store');
Route::put('/database/products-manage/{id}', [ReportController::class, 'updateProduct'])->name('products.update');
Route::delete('/database/products-manage/{id}', [ReportController::class, 'destroyProduct'])->name('products.destroy');

// Database - Material Breakdown
Route::get('/database/material-breakdown', [ReportController::class, 'materialBreakdown'])->name('material-breakdown');

// Database - Unpriced Parts
Route::get('/database/unpriced-parts', [ReportController::class, 'unpricedParts'])->name('unpriced-parts');

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
Route::get('/database/wires', [DatabaseController::class, 'wires'])->name('database.wires');
Route::post('/database/wires/switch-rate-month', [DatabaseController::class, 'switchWireRateMonth'])->name('database.wires.switch-rate-month');
Route::post('/database/wires/rates', [DatabaseController::class, 'storeWireRate'])->name('database.wires.rates.store');
Route::put('/database/wires/rates/{id}', [DatabaseController::class, 'updateWireRate'])->name('database.wires.rates.update');
Route::delete('/database/wires/rates/{id}', [DatabaseController::class, 'destroyWireRate'])->name('database.wires.rates.destroy');
Route::post('/database/wires', [DatabaseController::class, 'storeWire'])->name('database.wires.store');
Route::put('/database/wires/{id}', [DatabaseController::class, 'updateWire'])->name('database.wires.update');
Route::delete('/database/wires/{id}', [DatabaseController::class, 'destroyWire'])->name('database.wires.destroy');
Route::get('/database/costing', [DatabaseController::class, 'costing'])->name('database.costing');
Route::delete('/database/costing/{id}', [DatabaseController::class, 'destroyCosting'])->name('database.costing.destroy');
Route::get('/database/material-cost', [DatabaseController::class, 'materialCost'])->name('database.material-cost');
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
Route::get('/database/project-documents', [DatabaseController::class, 'projectDocuments'])->name('database.project-documents');
Route::put('/database/project-documents/{id}', [DatabaseController::class, 'updateProjectDocument'])->name('database.project-documents.update');
Route::delete('/database/project-documents/{id}', [DatabaseController::class, 'destroyProjectDocument'])->name('database.project-documents.destroy');
Route::patch('/costing/status-project/{revisionId}', [CostingController::class, 'updateStatusProject'])->name('costing.status-project.update');
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
Route::post('/tracking-documents/{revision}/add-version', [TrackingDocumentController::class, 'addVersion'])->name('tracking-documents.add-version');
Route::delete('/tracking-documents/{revision}/delete-version', [TrackingDocumentController::class, 'deleteVersion'])->name('tracking-documents.delete-version');
Route::post('/tracking-documents/{revision}/process-form-input', [TrackingDocumentController::class, 'processToFormInput'])->name('tracking-documents.process-form-input');
Route::post('/tracking-documents/{revision}/mark-cogm', [TrackingDocumentController::class, 'markCogmGenerated'])->name('tracking-documents.mark-cogm');
Route::post('/tracking-documents/{revision}/submit-cogm', [TrackingDocumentController::class, 'submitCogm'])->name('tracking-documents.submit-cogm');
Route::post('/tracking-documents/{revision}/update-files', [TrackingDocumentController::class, 'updateFiles'])->name('tracking-documents.update-files');
Route::post('/tracking-documents/{project}/update-project-info', [TrackingDocumentController::class, 'updateProjectInfo'])->name('tracking-documents.update-project-info');
Route::delete('/tracking-documents/{project}', [TrackingDocumentController::class, 'destroyProject'])->name('tracking-documents.destroy-project');
Route::post('/tracking-documents/{revision}/unpriced-price', [TrackingDocumentController::class, 'updateUnpricedPartPrice'])->name('tracking-documents.update-unpriced-price');
Route::post('/tracking-documents/{revision}/unpriced-delete', [TrackingDocumentController::class, 'deleteUnpricedPart'])->name('tracking-documents.delete-unpriced-part');
Route::post('/tracking-documents/{revision}/unpriced-bulk-delete', [TrackingDocumentController::class, 'bulkDeleteUnpricedParts'])->name('tracking-documents.bulk-delete-unpriced-parts');
Route::get('/tracking-documents/{revision}/{type}', [TrackingDocumentController::class, 'download'])
	->where('type', 'partlist|umh|a00|a04|a05')
	->name('tracking-documents.download');
Route::get('/tracking-documents/{revision}/export-unpriced/{format}', [TrackingDocumentController::class, 'exportUnpricedParts'])
	->where('format', 'excel|pdf')
	->name('tracking-documents.export-unpriced');

// Permission management (admin only)
Route::get('/permissions', [AuthController::class, 'permissions'])->name('permissions');
Route::post('/permissions', [AuthController::class, 'storeUser'])->name('permissions.store');
Route::post('/permissions/update-access', [AuthController::class, 'updatePermission'])->name('permissions.update-access');
Route::put('/permissions/{id}', [AuthController::class, 'updateUser'])->name('permissions.update');
Route::delete('/permissions/{id}', [AuthController::class, 'destroyUser'])->name('permissions.destroy');

}); // end auth middleware group
