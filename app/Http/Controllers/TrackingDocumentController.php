<?php

namespace App\Http\Controllers;

use App\Models\CogmSubmission;
use App\Models\BusinessCategory;
use App\Models\CostingData;
use App\Models\Customer;
use App\Models\DocumentProject;
use App\Models\DocumentRevision;
use App\Models\Material;
use App\Models\Plant;
use App\Models\Product;
use App\Models\UnpricedPart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrackingDocumentController extends Controller
{
    public function create()
    {
        $products = Product::orderBy('code')->get();
        $businessCategories = BusinessCategory::orderBy('code')->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $lines = Product::query()->whereNotNull('line')->distinct('line')->orderBy('line')->pluck('line');
        $plants = Plant::orderBy('code')->orderBy('name')->get();
        $periods = CostingData::distinct('period')->orderBy('period', 'desc')->pluck('period');

        return view('tracking-documents.create', compact('products', 'businessCategories', 'customers', 'lines', 'plants', 'periods'));
    }

    public function index()
    {
        $products = Product::orderBy('code')->get();
        $businessCategories = BusinessCategory::orderBy('code')->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $lines = Product::query()->whereNotNull('line')->distinct('line')->orderBy('line')->pluck('line');
        $plants = Plant::orderBy('code')->orderBy('name')->get();
        $periods = CostingData::distinct('period')->orderBy('period', 'desc')->pluck('period');

        $projects = DocumentProject::with([
            'product',
            'revisions' => function ($query) {
                $query->with(['cogmSubmissions', 'latestSubmission'])
                    ->orderBy('version_number', 'desc')
                    ->orderBy('id', 'desc');
            }
        ])->get()
            ->filter(fn ($project) => $project->revisions->isNotEmpty())
            ->sortBy(function ($project) {
                $latestRevisionDate = optional($project->revisions->first()?->received_date);
                return $latestRevisionDate?->timestamp ?? PHP_INT_MAX;
            })
            ->values();

        $revisions = $projects->flatMap(fn ($project) => $project->revisions)
            ->sortByDesc('id')
            ->values();

        return view('tracking-documents.index', compact('revisions', 'projects', 'products', 'businessCategories', 'customers', 'lines', 'plants', 'periods'));
    }

    public function storeReceipt(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id|required_without:business_category_id',
            'business_category_id' => 'nullable|exists:business_categories,id|required_without:product_id',
            'customer_id' => 'required|exists:customers,id',
            'model' => 'required|string|max:255',
            'assy_no' => 'required|string|max:255',
            'assy_name' => 'required|string|max:255',
            'forecast' => 'nullable|integer|min:0',
            'forecast_uom' => 'nullable|string|max:20',
            'forecast_basis' => 'nullable|string|max:20',
            'project_period' => 'nullable|integer|min:0',
            'line' => 'nullable|string|max:255',
            'period' => 'nullable|string|max:20',
            'received_date' => 'nullable|date',
            'pic_engineering' => 'required|string|max:255',
            'pic_marketing' => 'required|string|max:255',
            'a00_status' => 'nullable|in:ada,belum_ada',
            'a00_received_date' => 'nullable|date|required_if:a00_status,ada',
            'a00_document_file' => 'nullable|file|mimes:pdf|max:10240|required_if:a00_status,ada',
            'a04_status' => 'nullable|in:ada,belum_ada',
            'a04_received_date' => 'nullable|date|required_if:a04_status,ada',
            'a04_document_file' => 'nullable|file|mimes:pdf|max:10240|required_if:a04_status,ada',
            'a05_status' => 'nullable|in:ada,belum_ada',
            'a05_received_date' => 'nullable|date|required_if:a05_status,ada',
            'a05_document_file' => 'nullable|file|mimes:pdf|max:10240|required_if:a05_status,ada',
            'partlist_file' => 'nullable|file|mimes:xls,xlsx|max:10240',
            'umh_file' => 'nullable|file|mimes:xls,xlsx|max:10240',
            'notes' => 'nullable|string|max:1000',
            'change_remark' => 'nullable|string|max:1000',
        ], [
            'partlist_file.mimes' => 'Dokumen Partlist harus berformat Excel (xls/xlsx).',
            'umh_file.mimes' => 'Dokumen UMH harus berformat Excel (xls/xlsx).',
            'a00_document_file.mimes' => 'Dokumen A00 harus berformat PDF.',
            'a04_document_file.mimes' => 'Dokumen A04 harus berformat PDF.',
            'a05_document_file.mimes' => 'Dokumen A05 harus berformat PDF.',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $resolvedProduct = !empty($validated['business_category_id'])
                ? $this->resolveProductFromBusinessCategoryId((int) $validated['business_category_id'])
                : Product::findOrFail((int) $validated['product_id']);

            $customer = Customer::findOrFail((int) $validated['customer_id']);
            $customerName = trim((string) $customer->name);
            $a00Status = $validated['a00_status'] ?? 'belum_ada';
            $a04Status = $validated['a04_status'] ?? 'belum_ada';
            $a05Status = $validated['a05_status'] ?? 'belum_ada';

            $projectKey = $this->makeProjectKey(
                $customerName,
                $validated['model'],
                $validated['assy_no'],
                $validated['assy_name']
            );

            $project = DocumentProject::firstOrCreate(
                ['project_key' => $projectKey],
                [
                    'product_id' => $resolvedProduct->id,
                    'customer' => $customerName,
                    'model' => $validated['model'],
                    'part_number' => $validated['assy_no'],
                    'part_name' => $validated['assy_name'],
                ]
            );

            if ((int) ($project->product_id ?? 0) !== (int) $resolvedProduct->id) {
                $project->update([
                    'product_id' => $resolvedProduct->id,
                ]);
            }

            $nextVersion = (int) $project->revisions()->max('version_number') + 1;

            $partlistFile = $request->file('partlist_file');
            $umhFile = $request->file('umh_file');

            $a00DocumentPath = null;
            $a00DocumentOriginalName = null;
            if ($a00Status === 'ada' && $request->hasFile('a00_document_file')) {
                $a00DocumentFile = $request->file('a00_document_file');
                $a00DocumentPath = $a00DocumentFile->storeAs(
                    'tracking-documents/a00',
                    now()->format('YmdHis') . '-' . Str::uuid() . '.' . $a00DocumentFile->getClientOriginalExtension()
                );
                $a00DocumentOriginalName = $a00DocumentFile->getClientOriginalName();
            }

            $a05DocumentPath = null;
            $a05DocumentOriginalName = null;
            if ($a05Status === 'ada' && $request->hasFile('a05_document_file')) {
                $a05DocumentFile = $request->file('a05_document_file');
                $a05DocumentPath = $a05DocumentFile->storeAs(
                    'tracking-documents/a05',
                    now()->format('YmdHis') . '-' . Str::uuid() . '.' . $a05DocumentFile->getClientOriginalExtension()
                );
                $a05DocumentOriginalName = $a05DocumentFile->getClientOriginalName();
            }

            $a04DocumentPath = null;
            $a04DocumentOriginalName = null;
            if ($a04Status === 'ada' && $request->hasFile('a04_document_file')) {
                $a04DocumentFile = $request->file('a04_document_file');
                $a04DocumentPath = $a04DocumentFile->storeAs(
                    'tracking-documents/a04',
                    now()->format('YmdHis') . '-' . Str::uuid() . '.' . $a04DocumentFile->getClientOriginalExtension()
                );
                $a04DocumentOriginalName = $a04DocumentFile->getClientOriginalName();
            }

            $partlistPath = '';
            $partlistOriginalName = '';
            if ($partlistFile) {
                $partlistPath = $partlistFile->storeAs(
                    'tracking-documents/partlist',
                    now()->format('YmdHis') . '-' . Str::uuid() . '.' . $partlistFile->getClientOriginalExtension()
                );
                $partlistOriginalName = $partlistFile->getClientOriginalName();
            }

            $umhPath = '';
            $umhOriginalName = '';
            if ($umhFile) {
                $umhPath = $umhFile->storeAs(
                    'tracking-documents/umh',
                    now()->format('YmdHis') . '-' . Str::uuid() . '.' . $umhFile->getClientOriginalExtension()
                );
                $umhOriginalName = $umhFile->getClientOriginalName();
            }

            DocumentRevision::create([
                'document_project_id' => $project->id,
                'version_number' => $nextVersion,
                'received_date' => $validated['received_date'] ?? now()->toDateString(),
                'pic_engineering' => $validated['pic_engineering'],
                'pic_marketing' => $validated['pic_marketing'] ?? null,
                'a00' => $a00Status,
                'a00_received_date' => $a00Status === 'ada' ? ($validated['a00_received_date'] ?? null) : null,
                'a00_document_original_name' => $a00Status === 'ada' ? $a00DocumentOriginalName : null,
                'a00_document_file_path' => $a00Status === 'ada' ? $a00DocumentPath : null,
                'a04' => $a04Status,
                'a04_received_date' => $a04Status === 'ada' ? ($validated['a04_received_date'] ?? null) : null,
                'a04_document_original_name' => $a04Status === 'ada' ? $a04DocumentOriginalName : null,
                'a04_document_file_path' => $a04Status === 'ada' ? $a04DocumentPath : null,
                'a05' => $a05Status,
                'a05_received_date' => $a05Status === 'ada' ? ($validated['a05_received_date'] ?? null) : null,
                'a05_document_original_name' => $a05Status === 'ada' ? $a05DocumentOriginalName : null,
                'a05_document_file_path' => $a05Status === 'ada' ? $a05DocumentPath : null,
                'status' => DocumentRevision::STATUS_PENDING_FORM_INPUT,
                'partlist_original_name' => $partlistOriginalName,
                'partlist_file_path' => $partlistPath,
                'partlist_update_count' => 0,
                'partlist_updated_at' => null,
                'umh_original_name' => $umhOriginalName,
                'umh_file_path' => $umhPath,
                'umh_update_count' => 0,
                'umh_updated_at' => null,
                'notes' => $validated['notes'] ?? null,
                'change_remark' => $nextVersion === 1
                    ? 'Dokumen awal diterima (baseline V0).'
                    : ($validated['change_remark'] ?? 'Revisi Engineering diterima. Detail perubahan belum diisi.'),
            ]);
        });

        return redirect()->route('tracking-documents.index', absolute: false)
            ->with('success', 'Project baru berhasil dibuat.');
    }

    public function markCogmGenerated(DocumentRevision $revision)
    {
        $hasOpenUnpriced = UnpricedPart::where('document_revision_id', $revision->id)
            ->whereNull('resolved_at')
            ->exists();

        if ($hasOpenUnpriced) {
            $revision->update([
                'status' => DocumentRevision::STATUS_PENDING_PRICING,
            ]);

            return redirect()->back()
                ->with('warning', 'Masih ada part tanpa harga. Status tetap Draft / Pending Pricing.');
        }

        if (in_array($revision->status, [
            DocumentRevision::STATUS_PENDING_FORM_INPUT,
            DocumentRevision::STATUS_PENDING_PRICING,
        ], true)) {
            $revision->update([
                'status' => DocumentRevision::STATUS_COGM_GENERATED,
                'cogm_generated_at' => now(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Status berhasil diubah ke COGM Generated.');
    }

    public function processToFormInput(DocumentRevision $revision)
    {
        $revision->update([
            'status' => DocumentRevision::STATUS_PENDING_FORM_INPUT,
            'cogm_generated_at' => null,
        ]);

        $target = route('form', ['tracking_revision_id' => $revision->id], absolute: false);

        return response('', 302, ['Location' => $target]);
    }

    public function submitCogm(Request $request, DocumentRevision $revision)
    {
        $hasOpenUnpriced = UnpricedPart::where('document_revision_id', $revision->id)
            ->whereNull('resolved_at')
            ->exists();

        if ($hasOpenUnpriced) {
            return redirect()->back()
                ->with('warning', 'Submit COGM ditolak karena masih ada part tanpa harga pada revisi ini.');
        }

        $validated = $request->validate([
            'pic_marketing' => 'required|string|max:255',
            'cogm_value' => 'nullable|numeric',
            'submitted_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        CogmSubmission::create([
            'document_revision_id' => $revision->id,
            'submitted_at' => now(),
            'pic_marketing' => $validated['pic_marketing'],
            'cogm_value' => $validated['cogm_value'] ?? null,
            'submitted_by' => $validated['submitted_by'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $revision->update([
            'status' => DocumentRevision::STATUS_SUBMITTED_TO_MARKETING,
            'pic_marketing' => $validated['pic_marketing'],
        ]);

        return redirect()->back()
            ->with('success', 'COGM berhasil disubmit ke Marketing.');
    }

    public function updateFiles(Request $request, DocumentRevision $revision)
    {
        $validated = $request->validate([
            'partlist_file' => 'nullable|file|mimes:xls,xlsx|max:10240',
            'umh_file' => 'nullable|file|mimes:xls,xlsx|max:10240',
            'change_remark' => 'nullable|string|max:1000',
        ], [
            'partlist_file.mimes' => 'Dokumen Partlist harus berformat Excel (xls/xlsx).',
            'umh_file.mimes' => 'Dokumen UMH harus berformat Excel (xls/xlsx).',
        ]);

        if (!$request->hasFile('partlist_file') && !$request->hasFile('umh_file')) {
            return redirect()->back()->with('warning', 'Pilih minimal satu file (Partlist atau UMH) untuk diupdate.');
        }

        $updatedRevision = DB::transaction(function () use ($request, $revision) {
            $targetRevision = DocumentRevision::query()
                ->whereKey($revision->id)
                ->lockForUpdate()
                ->firstOrFail();

            $partlistPath = $targetRevision->partlist_file_path;
            $partlistOriginalName = $targetRevision->partlist_original_name;
            $umhPath = $targetRevision->umh_file_path;
            $umhOriginalName = $targetRevision->umh_original_name;

            if ($request->hasFile('partlist_file')) {
                $partlistFile = $request->file('partlist_file');
                if ($partlistPath && Storage::exists($partlistPath)) {
                    Storage::delete($partlistPath);
                }
                $partlistPath = $partlistFile->storeAs(
                    'tracking-documents/partlist',
                    now()->format('YmdHis') . '-' . Str::uuid() . '.' . $partlistFile->getClientOriginalExtension()
                );
                $partlistOriginalName = $partlistFile->getClientOriginalName();
            }

            if ($request->hasFile('umh_file')) {
                $umhFile = $request->file('umh_file');
                if ($umhPath && Storage::exists($umhPath)) {
                    Storage::delete($umhPath);
                }
                $umhPath = $umhFile->storeAs(
                    'tracking-documents/umh',
                    now()->format('YmdHis') . '-' . Str::uuid() . '.' . $umhFile->getClientOriginalExtension()
                );
                $umhOriginalName = $umhFile->getClientOriginalName();
            }

            $targetRevision->update([
                'partlist_original_name' => $partlistOriginalName,
                'partlist_file_path' => $partlistPath,
                'partlist_update_count' => $request->hasFile('partlist_file')
                    ? ((int) ($targetRevision->partlist_update_count ?? 0) + 1)
                    : (int) ($targetRevision->partlist_update_count ?? 0),
                'partlist_updated_at' => $request->hasFile('partlist_file') ? now() : $targetRevision->partlist_updated_at,
                'umh_original_name' => $umhOriginalName,
                'umh_file_path' => $umhPath,
                'umh_update_count' => $request->hasFile('umh_file')
                    ? ((int) ($targetRevision->umh_update_count ?? 0) + 1)
                    : (int) ($targetRevision->umh_update_count ?? 0),
                'umh_updated_at' => $request->hasFile('umh_file') ? now() : $targetRevision->umh_updated_at,
                'change_remark' => trim((string) ($validated['change_remark'] ?? '')) !== ''
                    ? trim((string) $validated['change_remark'])
                    : '-',
            ]);

            return $targetRevision->fresh();
        });

        return redirect()->back()->with('success', 'Dokumen pada ' . $updatedRevision->version_label . ' berhasil diperbarui.');
    }

    public function addVersion(DocumentRevision $revision)
    {
        $newRevision = DB::transaction(function () use ($revision) {
            $project = $revision->project()->lockForUpdate()->firstOrFail();

            $baseRevision = $project->revisions()
                ->orderByDesc('version_number')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->firstOrFail();

            $nextVersion = (int) $project->revisions()->max('version_number') + 1;

            return DocumentRevision::create([
                'document_project_id' => $project->id,
                'version_number' => $nextVersion,
                'received_date' => now()->toDateString(),
                'pic_engineering' => $baseRevision->pic_engineering,
                'status' => DocumentRevision::STATUS_PENDING_FORM_INPUT,
                'pic_marketing' => $baseRevision->pic_marketing,
                'a00' => $baseRevision->a00,
                'a00_received_date' => $baseRevision->a00_received_date,
                'a00_document_original_name' => $baseRevision->a00_document_original_name,
                'a00_document_file_path' => $baseRevision->a00_document_file_path,
                'a04' => $baseRevision->a04,
                'a04_received_date' => $baseRevision->a04_received_date,
                'a04_document_original_name' => $baseRevision->a04_document_original_name,
                'a04_document_file_path' => $baseRevision->a04_document_file_path,
                'a05' => $baseRevision->a05,
                'a05_received_date' => $baseRevision->a05_received_date,
                'a05_document_original_name' => $baseRevision->a05_document_original_name,
                'a05_document_file_path' => $baseRevision->a05_document_file_path,
                'partlist_original_name' => $baseRevision->partlist_original_name,
                'partlist_file_path' => $baseRevision->partlist_file_path,
                'partlist_update_count' => 0,
                'partlist_updated_at' => null,
                'umh_original_name' => $baseRevision->umh_original_name,
                'umh_file_path' => $baseRevision->umh_file_path,
                'umh_update_count' => 0,
                'umh_updated_at' => null,
                'notes' => $baseRevision->notes,
                'change_remark' => 'Revisi Engineering diterima. Versi baru dibuat.',
            ]);
        });

        return redirect()->back()->with('success', 'Versi baru ' . $newRevision->version_label . ' berhasil ditambahkan.');
    }

    public function deleteVersion(DocumentRevision $revision)
    {
        $result = DB::transaction(function () use ($revision) {
            $project = $revision->project()->lockForUpdate()->firstOrFail();

            $targetRevision = $project->revisions()
                ->whereKey($revision->id)
                ->lockForUpdate()
                ->first();

            if (!$targetRevision) {
                return [
                    'deleted' => false,
                    'reason' => 'not_found',
                ];
            }

            $revisionCount = $project->revisions()->lockForUpdate()->count();
            if ($revisionCount <= 1) {
                return [
                    'deleted' => false,
                    'reason' => 'last_version',
                ];
            }

            $filePaths = collect([
                $targetRevision->partlist_file_path,
                $targetRevision->umh_file_path,
                $targetRevision->a00_document_file_path,
                $targetRevision->a04_document_file_path,
                $targetRevision->a05_document_file_path,
            ])->filter()->unique()->values();

            $deletedVersionLabel = $targetRevision->version_label;
            $targetRevisionId = (int) $targetRevision->id;
            $targetRevision->delete();

            foreach ($filePaths as $path) {
                $isStillUsed = DocumentRevision::query()
                    ->where(function ($query) use ($path) {
                        $query->where('partlist_file_path', $path)
                            ->orWhere('umh_file_path', $path)
                            ->orWhere('a00_document_file_path', $path)
                            ->orWhere('a04_document_file_path', $path)
                            ->orWhere('a05_document_file_path', $path);
                    })
                    ->exists();

                if (!$isStillUsed && Storage::exists($path)) {
                    Storage::delete($path);
                }
            }

            return [
                'deleted' => true,
                'version_label' => $deletedVersionLabel,
                'revision_id' => $targetRevisionId,
            ];
        });

        if (!($result['deleted'] ?? false)) {
            if (($result['reason'] ?? '') === 'last_version') {
                return redirect()->back()->with('warning', 'Versi tidak bisa dihapus karena project harus memiliki minimal satu versi.');
            }

            return redirect()->back()->with('warning', 'Versi tidak ditemukan atau sudah terhapus.');
        }

        return redirect()->back()->with('success', 'Versi ' . ($result['version_label'] ?? '') . ' berhasil dihapus.');
    }

    public function updateProjectInfo(Request $request, DocumentProject $project)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id|required_without:business_category_id',
            'business_category_id' => 'nullable|exists:business_categories,id|required_without:product_id',
            'customer_id' => 'required|exists:customers,id',
            'model' => 'required|string|max:255',
            'part_number' => 'required|string|max:255',
            'part_name' => 'required|string|max:255',
            'received_date' => 'nullable|date',
            'pic_engineering' => 'required|string|max:255',
            'pic_marketing' => 'required|string|max:255',
            'a00' => 'nullable|in:ada,belum_ada',
            'a00_document_file' => 'nullable|file|mimes:pdf|max:10240|required_if:a00,ada',
            'a04' => 'nullable|in:ada,belum_ada',
            'a04_document_file' => 'nullable|file|mimes:pdf|max:10240|required_if:a04,ada',
            'a05' => 'nullable|in:ada,belum_ada',
            'a05_document_file' => 'nullable|file|mimes:pdf|max:10240|required_if:a05,ada',
        ], [
            'a00_document_file.mimes' => 'Dokumen A00 harus berformat PDF.',
            'a04_document_file.mimes' => 'Dokumen A04 harus berformat PDF.',
            'a05_document_file.mimes' => 'Dokumen A05 harus berformat PDF.',
        ]);

        $customer = Customer::findOrFail((int) $validated['customer_id']);

        $normalizedCustomer = trim((string) $customer->name);
        $normalizedModel = trim((string) $validated['model']);
        $normalizedPartNumber = trim((string) $validated['part_number']);
        $normalizedPartName = trim((string) $validated['part_name']);

        $nextProjectKey = $this->makeProjectKey(
            $normalizedCustomer,
            $normalizedModel,
            $normalizedPartNumber,
            $normalizedPartName
        );

        $duplicateExists = DocumentProject::query()
            ->where('project_key', $nextProjectKey)
            ->where('id', '!=', $project->id)
            ->exists();

        if ($duplicateExists) {
            return redirect()->back()->with('warning', 'Informasi project sama persis dengan project lain yang sudah ada.');
        }

        DB::transaction(function () use ($project, $validated, $request, $normalizedCustomer, $normalizedModel, $normalizedPartNumber, $normalizedPartName, $nextProjectKey) {
            $resolvedProduct = !empty($validated['business_category_id'])
                ? $this->resolveProductFromBusinessCategoryId((int) $validated['business_category_id'])
                : Product::findOrFail((int) $validated['product_id']);

            $project->update([
                'product_id' => $resolvedProduct->id,
                'customer' => $normalizedCustomer,
                'model' => $normalizedModel,
                'part_number' => $normalizedPartNumber,
                'part_name' => $normalizedPartName,
                'project_key' => $nextProjectKey,
            ]);

            $latestRevision = $project->revisions()
                ->orderByDesc('version_number')
                ->orderByDesc('id')
                ->first();

            if ($latestRevision) {
                $a00DocumentPath = $latestRevision->a00_document_file_path;
                $a00DocumentOriginalName = $latestRevision->a00_document_original_name;
                $a04DocumentPath = $latestRevision->a04_document_file_path;
                $a04DocumentOriginalName = $latestRevision->a04_document_original_name;
                $a05DocumentPath = $latestRevision->a05_document_file_path;
                $a05DocumentOriginalName = $latestRevision->a05_document_original_name;

                if (($validated['a00'] ?? 'belum_ada') !== 'ada') {
                    $a00DocumentPath = null;
                    $a00DocumentOriginalName = null;
                } elseif ($request->hasFile('a00_document_file')) {
                    $a00DocumentFile = $request->file('a00_document_file');
                    $a00DocumentPath = $a00DocumentFile->storeAs(
                        'tracking-documents/a00',
                        now()->format('YmdHis') . '-' . Str::uuid() . '.' . $a00DocumentFile->getClientOriginalExtension()
                    );
                    $a00DocumentOriginalName = $a00DocumentFile->getClientOriginalName();
                }

                if (($validated['a04'] ?? 'belum_ada') !== 'ada') {
                    $a04DocumentPath = null;
                    $a04DocumentOriginalName = null;
                } elseif ($request->hasFile('a04_document_file')) {
                    $a04DocumentFile = $request->file('a04_document_file');
                    $a04DocumentPath = $a04DocumentFile->storeAs(
                        'tracking-documents/a04',
                        now()->format('YmdHis') . '-' . Str::uuid() . '.' . $a04DocumentFile->getClientOriginalExtension()
                    );
                    $a04DocumentOriginalName = $a04DocumentFile->getClientOriginalName();
                }

                if (($validated['a05'] ?? 'belum_ada') !== 'ada') {
                    $a05DocumentPath = null;
                    $a05DocumentOriginalName = null;
                } elseif ($request->hasFile('a05_document_file')) {
                    $a05DocumentFile = $request->file('a05_document_file');
                    $a05DocumentPath = $a05DocumentFile->storeAs(
                        'tracking-documents/a05',
                        now()->format('YmdHis') . '-' . Str::uuid() . '.' . $a05DocumentFile->getClientOriginalExtension()
                    );
                    $a05DocumentOriginalName = $a05DocumentFile->getClientOriginalName();
                }

                $latestRevision->update([
                    'received_date' => $validated['received_date'] ?? $latestRevision->received_date,
                    'pic_engineering' => $validated['pic_engineering'],
                    'pic_marketing' => $validated['pic_marketing'],
                    'a00' => $validated['a00'] ?? ($latestRevision->a00 ?: 'belum_ada'),
                    'a00_document_original_name' => $a00DocumentOriginalName,
                    'a00_document_file_path' => $a00DocumentPath,
                    'a04' => $validated['a04'] ?? ($latestRevision->a04 ?: 'belum_ada'),
                    'a04_document_original_name' => $a04DocumentOriginalName,
                    'a04_document_file_path' => $a04DocumentPath,
                    'a05' => $validated['a05'] ?? ($latestRevision->a05 ?: 'belum_ada'),
                    'a05_document_original_name' => $a05DocumentOriginalName,
                    'a05_document_file_path' => $a05DocumentPath,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Informasi project berhasil diperbarui.');
    }

    public function destroyProject(DocumentProject $project)
    {
        DB::transaction(function () use ($project) {
            $filePaths = $project->revisions()
                ->get([
                    'partlist_file_path',
                    'umh_file_path',
                    'a00_document_file_path',
                    'a04_document_file_path',
                    'a05_document_file_path',
                ])
                ->flatMap(function ($revision) {
                    return [
                        $revision->partlist_file_path,
                        $revision->umh_file_path,
                        $revision->a00_document_file_path,
                        $revision->a04_document_file_path,
                        $revision->a05_document_file_path,
                    ];
                })
                ->filter()
                ->unique()
                ->values();

            foreach ($filePaths as $path) {
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
            }

            $project->delete();
        });

        return redirect()->back()->with('success', 'Semua data project berhasil dihapus.');
    }

    public function exportUnpricedParts(DocumentRevision $revision, string $format)
    {
        $rows = UnpricedPart::where('document_revision_id', $revision->id)
            ->whereNull('resolved_at')
            ->orderBy('part_number')
            ->get();

        if ($format === 'excel') {
            $filename = 'unpriced-parts-' . $revision->id . '-v' . $revision->version_number . '.csv';

            $csv = collect([
                ['Part Number', 'Part Name', 'Detected Price'],
            ])->concat($rows->map(function ($item) {
                return [
                    $item->part_number,
                    $item->part_name,
                    (string) ($item->detected_price ?? 0),
                ];
            }))->map(function ($line) {
                return collect($line)->map(function ($cell) {
                    $escaped = str_replace('"', '""', (string) $cell);
                    return '"' . $escaped . '"';
                })->implode(',');
            })->implode("\n");

            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename=' . $filename);
        }

        $html = view('tracking-documents.unpriced-parts-pdf', [
            'revision' => $revision,
            'rows' => $rows,
        ])->render();

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function updateUnpricedPartPrice(Request $request, DocumentRevision $revision)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|max:255',
            'manual_price' => 'nullable|numeric|min:0',
            'use_database_lookup' => 'nullable|boolean',
        ]);

        $partNumber = trim((string) $validated['part_number']);
        $partKey = strtolower($partNumber);
        $manualPrice = floatval($validated['manual_price'] ?? 0);
        $useDatabaseLookup = (bool) ($validated['use_database_lookup'] ?? false);
        $appliedPrice = $manualPrice;
        $appliedCurrency = '';
        $appliedPurchaseUnit = '';
        $appliedMoq = null;
        $appliedCn = '';
        $appliedMaker = '';
        $appliedAddCostImportTax = null;
        $resolutionSource = 'realtime_manual_input';

        DB::transaction(function () use ($revision, $partKey, $partNumber, $manualPrice, $useDatabaseLookup, &$appliedPrice, &$appliedCurrency, &$appliedPurchaseUnit, &$appliedMoq, &$appliedCn, &$appliedMaker, &$appliedAddCostImportTax, &$resolutionSource) {
            $openRows = UnpricedPart::where('document_revision_id', $revision->id)
                ->whereNull('resolved_at')
                ->whereRaw('lower(part_number) = ?', [$partKey])
                ->get();

            $partName = trim((string) ($openRows->first()?->part_name ?? ''));

            if ($appliedPrice <= 0 && $useDatabaseLookup) {
                $matchedMaterial = $this->findMaterialForUnpricedPart($partNumber, $partName);
                if ($matchedMaterial) {
                    $appliedPrice = floatval($matchedMaterial->price ?? 0);
                    $appliedCurrency = trim((string) ($matchedMaterial->currency ?? ''));
                    $appliedPurchaseUnit = trim((string) ($matchedMaterial->purchase_unit ?? ''));
                    $appliedMoq = $matchedMaterial->moq;
                    $appliedCn = trim((string) ($matchedMaterial->cn ?? ''));
                    $appliedMaker = trim((string) ($matchedMaterial->maker ?? ''));
                    $appliedAddCostImportTax = $matchedMaterial->add_cost_import_tax;
                    $resolutionSource = 'realtime_db_lookup';
                }
            }

            if ($appliedPrice > 0) {

                $material = Material::firstOrCreate(
                    ['material_code' => $partNumber],
                    [
                        'material_description' => $partName ?: null,
                        'base_uom' => 'PCS',
                        'currency' => $appliedCurrency !== '' ? $appliedCurrency : 'IDR',
                        'price' => 0,
                    ]
                );

                $material->price = $appliedPrice;
                if ($appliedCurrency !== '') {
                    $material->currency = $appliedCurrency;
                }
                $material->price_update = now()->toDateString();
                $material->save();

                foreach ($openRows as $row) {
                    $row->update([
                        'manual_price' => $appliedPrice,
                        'resolved_at' => now(),
                        'resolution_source' => $resolutionSource,
                    ]);
                }
            } else {
                foreach ($openRows as $row) {
                    $row->update([
                        'manual_price' => null,
                    ]);
                }
            }

            $hasOpenUnpriced = UnpricedPart::where('document_revision_id', $revision->id)
                ->whereNull('resolved_at')
                ->exists();

            if ($hasOpenUnpriced) {
                if ($revision->status !== DocumentRevision::STATUS_SUBMITTED_TO_MARKETING) {
                    $revision->update([
                        'status' => DocumentRevision::STATUS_PENDING_PRICING,
                    ]);
                }
            } elseif ($revision->status === DocumentRevision::STATUS_PENDING_PRICING) {
                $revision->update([
                    'status' => DocumentRevision::STATUS_COGM_GENERATED,
                    'cogm_generated_at' => now(),
                ]);
            }
        });

        $openCount = UnpricedPart::where('document_revision_id', $revision->id)
            ->whereNull('resolved_at')
            ->count();

        return response()->json([
            'ok' => true,
            'open_unpriced_count' => $openCount,
            'status' => $revision->fresh()->status,
            'status_label' => $revision->fresh()->status_label,
            'applied_price' => $appliedPrice,
            'applied_currency' => $appliedCurrency,
            'applied_purchase_unit' => $appliedPurchaseUnit,
            'applied_moq' => $appliedMoq,
            'applied_cn' => $appliedCn,
            'applied_maker' => $appliedMaker,
            'applied_add_cost_import_tax' => $appliedAddCostImportTax,
            'resolution_source' => $resolutionSource,
        ]);
    }

    public function deleteUnpricedPart(Request $request, DocumentRevision $revision)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|max:255',
        ]);

        $partKey = strtolower(trim((string) $validated['part_number']));

        DB::transaction(function () use ($revision, $partKey) {
            UnpricedPart::where('document_revision_id', $revision->id)
                ->whereNull('resolved_at')
                ->whereRaw('lower(part_number) = ?', [$partKey])
                ->update([
                    'resolved_at' => now(),
                    'resolution_source' => 'manual_delete',
                ]);

            $hasOpenUnpriced = UnpricedPart::where('document_revision_id', $revision->id)
                ->whereNull('resolved_at')
                ->exists();

            if ($hasOpenUnpriced) {
                if ($revision->status !== DocumentRevision::STATUS_SUBMITTED_TO_MARKETING) {
                    $revision->update([
                        'status' => DocumentRevision::STATUS_PENDING_PRICING,
                    ]);
                }
            } elseif ($revision->status === DocumentRevision::STATUS_PENDING_PRICING) {
                $revision->update([
                    'status' => DocumentRevision::STATUS_COGM_GENERATED,
                    'cogm_generated_at' => now(),
                ]);
            }
        });

        $openCount = UnpricedPart::where('document_revision_id', $revision->id)
            ->whereNull('resolved_at')
            ->count();

        return response()->json([
            'ok' => true,
            'open_unpriced_count' => $openCount,
            'status' => $revision->fresh()->status,
            'status_label' => $revision->fresh()->status_label,
        ]);
    }

    public function restoreUnpricedPart(Request $request, DocumentRevision $revision)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|max:255',
        ]);

        $partKey = strtolower(trim((string) $validated['part_number']));
        $restored = false;

        DB::transaction(function () use ($revision, $partKey, &$restored) {
            $target = UnpricedPart::where('document_revision_id', $revision->id)
                ->whereNotNull('resolved_at')
                ->whereRaw('lower(part_number) = ?', [$partKey])
                ->orderByDesc('resolved_at')
                ->orderByDesc('id')
                ->first();

            if ($target) {
                $target->update([
                    'manual_price' => null,
                    'resolved_at' => null,
                    'resolution_source' => 'undo_tambah',
                ]);
                $restored = true;
            }

            $hasOpenUnpriced = UnpricedPart::where('document_revision_id', $revision->id)
                ->whereNull('resolved_at')
                ->exists();

            if ($hasOpenUnpriced) {
                if ($revision->status !== DocumentRevision::STATUS_SUBMITTED_TO_MARKETING) {
                    $revision->update([
                        'status' => DocumentRevision::STATUS_PENDING_PRICING,
                    ]);
                }
            } elseif ($revision->status === DocumentRevision::STATUS_PENDING_PRICING) {
                $revision->update([
                    'status' => DocumentRevision::STATUS_COGM_GENERATED,
                    'cogm_generated_at' => now(),
                ]);
            }
        });

        $openCount = UnpricedPart::where('document_revision_id', $revision->id)
            ->whereNull('resolved_at')
            ->count();

        return response()->json([
            'ok' => true,
            'restored' => $restored,
            'open_unpriced_count' => $openCount,
            'status' => $revision->fresh()->status,
            'status_label' => $revision->fresh()->status_label,
        ]);
    }

    public function download(DocumentRevision $revision, string $type)
    {
        if (!in_array($type, ['partlist', 'umh', 'a00', 'a04', 'a05'], true)) {
            abort(404);
        }

        $fieldMap = [
            'partlist' => ['path' => 'partlist_file_path', 'name' => 'partlist_original_name'],
            'umh' => ['path' => 'umh_file_path', 'name' => 'umh_original_name'],
            'a00' => ['path' => 'a00_document_file_path', 'name' => 'a00_document_original_name'],
            'a04' => ['path' => 'a04_document_file_path', 'name' => 'a04_document_original_name'],
            'a05' => ['path' => 'a05_document_file_path', 'name' => 'a05_document_original_name'],
        ];

        $pathField = $fieldMap[$type]['path'];
        $nameField = $fieldMap[$type]['name'];

        $path = $revision->{$pathField};
        $name = $revision->{$nameField};

        if (!$path || !Storage::exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::download($path, $name);
    }

    public function viewDocument(DocumentRevision $revision, string $type)
    {
        if (!in_array($type, ['partlist', 'umh', 'a00', 'a04', 'a05'], true)) {
            abort(404);
        }

        $fieldMap = [
            'partlist' => ['path' => 'partlist_file_path', 'name' => 'partlist_original_name'],
            'umh' => ['path' => 'umh_file_path', 'name' => 'umh_original_name'],
            'a00' => ['path' => 'a00_document_file_path', 'name' => 'a00_document_original_name'],
            'a04' => ['path' => 'a04_document_file_path', 'name' => 'a04_document_original_name'],
            'a05' => ['path' => 'a05_document_file_path', 'name' => 'a05_document_original_name'],
        ];

        $pathField = $fieldMap[$type]['path'];
        $nameField = $fieldMap[$type]['name'];

        $path = $revision->{$pathField};
        $name = $revision->{$nameField};

        if (!$path || !Storage::exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $absolutePath = Storage::path($path);
        $mimeType = Storage::mimeType($path) ?: 'application/octet-stream';
        $safeName = str_replace('"', '\\"', (string) ($name ?: basename($path)));

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $safeName . '"',
        ]);
    }

    private function findMaterialForUnpricedPart(string $partNumber, string $partName = ''): ?Material
    {
        $normalizedPartNumber = trim($partNumber);
        $normalizedPartName = trim($partName);

        if ($normalizedPartNumber !== '') {
            $directByCode = Material::query()
                ->whereRaw('lower(material_code) = ?', [Str::lower($normalizedPartNumber)])
                ->where('price', '>', 0)
                ->orderByRaw('CASE WHEN price_update IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('price_update')
                ->orderByDesc('id')
                ->first();

            if ($directByCode) {
                return $directByCode;
            }

            $escapedPartNumber = $this->escapeLikeKeyword($normalizedPartNumber);

            $byDescriptionFromPartNumber = Material::query()
                ->where('price', '>', 0)
                ->where(function ($query) use ($normalizedPartNumber, $escapedPartNumber) {
                    $query->whereRaw('lower(material_description) = ?', [Str::lower($normalizedPartNumber)])
                        ->orWhereRaw('lower(material_description) like ?', ['%' . Str::lower($escapedPartNumber) . '%']);
                })
                ->orderByRaw('CASE WHEN price_update IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('price_update')
                ->orderByDesc('id')
                ->first();

            if ($byDescriptionFromPartNumber) {
                return $byDescriptionFromPartNumber;
            }
        }

        if ($normalizedPartName !== '') {
            $escapedPartName = $this->escapeLikeKeyword($normalizedPartName);

            $byDescriptionFromPartName = Material::query()
                ->where('price', '>', 0)
                ->where(function ($query) use ($normalizedPartName, $escapedPartName) {
                    $query->whereRaw('lower(material_description) = ?', [Str::lower($normalizedPartName)])
                        ->orWhereRaw('lower(material_description) like ?', ['%' . Str::lower($escapedPartName) . '%']);
                })
                ->orderByRaw('CASE WHEN price_update IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('price_update')
                ->orderByDesc('id')
                ->first();

            if ($byDescriptionFromPartName) {
                return $byDescriptionFromPartName;
            }
        }

        $normalizedPartNumberKey = $this->normalizeLookupKey($normalizedPartNumber);
        $normalizedPartNameKey = $this->normalizeLookupKey($normalizedPartName);

        if ($normalizedPartNumberKey === '' && $normalizedPartNameKey === '') {
            return null;
        }

        $searchSource = trim($normalizedPartNumber . ' ' . $normalizedPartName);
        $tokenCandidates = collect(preg_split('/[^a-z0-9]+/i', Str::lower($searchSource)) ?: [])
            ->map(fn ($token) => trim((string) $token))
            ->filter(fn ($token) => strlen($token) >= 3)
            ->unique()
            ->values();

        $candidateQuery = Material::query()
            ->where('price', '>', 0)
            ->where(function ($query) {
                $query->whereNotNull('material_code')
                    ->orWhereNotNull('material_description');
            });

        if ($tokenCandidates->isNotEmpty()) {
            $candidateQuery->where(function ($query) use ($tokenCandidates) {
                foreach ($tokenCandidates as $token) {
                    $escapedToken = $this->escapeLikeKeyword((string) $token);
                    $query->orWhereRaw('lower(material_code) like ?', ['%' . $escapedToken . '%'])
                        ->orWhereRaw('lower(material_description) like ?', ['%' . $escapedToken . '%']);
                }
            });
        }

        $candidates = $candidateQuery
            ->orderByRaw('CASE WHEN price_update IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('price_update')
            ->orderByDesc('id')
            ->limit(3000)
            ->get();

        foreach ($candidates as $candidate) {
            $candidateCodeKey = $this->normalizeLookupKey((string) ($candidate->material_code ?? ''));
            $candidateDescriptionKey = $this->normalizeLookupKey((string) ($candidate->material_description ?? ''));

            if ($this->isNormalizedLookupMatch($normalizedPartNumberKey, $candidateCodeKey)
                || $this->isNormalizedLookupMatch($normalizedPartNumberKey, $candidateDescriptionKey)
                || $this->isNormalizedLookupMatch($normalizedPartNameKey, $candidateDescriptionKey)) {
                return $candidate;
            }
        }

        return null;
    }

    private function escapeLikeKeyword(string $keyword): string
    {
        return addcslashes($keyword, '\\%_');
    }

    private function normalizeLookupKey(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::lower(trim($value))) ?? '';
    }

    private function isNormalizedLookupMatch(string $sourceKey, string $targetKey): bool
    {
        if ($sourceKey === '' || $targetKey === '') {
            return false;
        }

        if ($sourceKey === $targetKey) {
            return true;
        }

        return str_contains($sourceKey, $targetKey) || str_contains($targetKey, $sourceKey);
    }

    private function makeProjectKey(string $customer, string $model, string $partNumber, string $partName): string
    {
        $raw = collect([$customer, $model, $partNumber, $partName])
            ->map(fn ($value) => Str::lower(trim($value)))
            ->implode('|');

        return hash('sha256', $raw);
    }

    private function resolveProductFromBusinessCategoryId(int $businessCategoryId): Product
    {
        $businessCategory = BusinessCategory::findOrFail($businessCategoryId);
        $code = trim((string) $businessCategory->code);
        $name = trim((string) $businessCategory->name);

        $product = Product::firstOrCreate(
            ['code' => $code],
            ['name' => $name]
        );

        if (trim((string) $product->name) !== $name) {
            $product->update(['name' => $name]);
        }

        return $product;
    }
}
