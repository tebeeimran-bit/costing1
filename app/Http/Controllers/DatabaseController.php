<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Plant;
use App\Models\Pic;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Wire;
use App\Models\WireRate;
use App\Models\BusinessCategory;
use App\Models\CostingData;
use App\Models\CycleTimeTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DatabaseController extends Controller
{
    public function index()
    {
        return redirect(route('database.products', absolute: false));
    }

    public function products()
    {
        $products = Product::all();
        return view('database.products', compact('products'));
    }

    public function parts(Request $request)
    {
        $keyword = trim((string) $request->input('q', ''));

        $perPage = (int) $request->input('per_page', 100);
        if ($perPage <= 0) {
            $perPage = 100;
        }
        if ($perPage > 500) {
            $perPage = 500;
        }

        $materialsQuery = Material::query();

        if ($keyword !== '') {
            $materialsQuery->where(function ($query) use ($keyword) {
                $like = '%' . $keyword . '%';
                $query->where('material_code', 'like', $like)
                    ->orWhere('material_description', 'like', $like)
                    ->orWhere('material_type', 'like', $like)
                    ->orWhere('material_group', 'like', $like)
                    ->orWhere('maker', 'like', $like);
            });
        }

        $materials = $materialsQuery
            ->orderBy('material_code', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return view('database.parts', compact('materials'));
    }

    public function costing()
    {
        $costingData = CostingData::with(['product', 'customer'])->get();
        return view('database.costing', compact('costingData'));
    }

    public function customers()
    {
        $customers = Customer::all();
        return view('database.customers', compact('customers'));
    }

    public function storeCustomer(Request $request)
    {
        $validated = $request->validateWithBag('customerCreate', [
            'code' => 'required|string|max:255|unique:customers,code',
            'name' => 'required|string|max:255|unique:customers,name',
        ]);

        Customer::create([
            'code' => trim((string) $validated['code']),
            'name' => trim((string) $validated['name']),
        ]);

        return back()
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function updateCustomer(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:customers,code,' . $id,
            'name' => 'required|string|max:255|unique:customers,name,' . $id,
        ]);

        $customer->update([
            'code' => trim((string) $validated['code']),
            'name' => trim((string) $validated['name']),
        ]);

        return back()
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroyCustomer($id)
    {
        $customer = Customer::findOrFail($id);

        $isUsed = CostingData::where('customer_id', $customer->id)->exists();
        if ($isUsed) {
            return back()
                ->with('warning', 'Customer tidak bisa dihapus karena sudah digunakan pada data costing.');
        }

        $customer->delete();

        return back()
            ->with('success', 'Customer berhasil dihapus.');
    }

    public function cycleTimeTemplates()
    {
        $templates = CycleTimeTemplate::orderBy('id')->get();
        return view('database.cycle-time-templates', compact('templates'));
    }

    public function storeCycleTimeTemplate(Request $request)
    {
        $validated = $request->validate([
            'process' => 'required|string|max:255|unique:cycle_time_templates,process',
        ]);

        CycleTimeTemplate::create($validated);

        return redirect(route('database.cycle-time-templates', absolute: false))
            ->with('success', 'Process template berhasil ditambahkan!');
    }

    public function updateCycleTimeTemplate(Request $request, $id)
    {
        $template = CycleTimeTemplate::findOrFail($id);

        $validated = $request->validate([
            'process' => 'required|string|max:255|unique:cycle_time_templates,process,' . $id,
        ]);

        $template->update([
            'process' => trim((string) $validated['process']),
        ]);

        return redirect(route('database.cycle-time-templates', absolute: false))
            ->with('success', 'Process template berhasil diperbarui!');
    }

    public function destroyCycleTimeTemplate($id)
    {
        $template = CycleTimeTemplate::findOrFail($id);
        $template->delete();

        return redirect(route('database.cycle-time-templates', absolute: false))
            ->with('success', 'Process template berhasil dihapus!');
    }

    public function businessCategories()
    {
        $businessCategories = BusinessCategory::orderBy('code')->orderBy('name')->get();
        return view('database.business-categories', compact('businessCategories'));
    }

    public function storeBusinessCategory(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:business_categories,code',
            'name' => 'required|string|max:255|unique:business_categories,name',
        ]);

        BusinessCategory::create([
            'code' => trim((string) $validated['code']),
            'name' => trim((string) $validated['name']),
        ]);

        return back()->with('success', 'Business Category berhasil ditambahkan.');
    }

    public function updateBusinessCategory(Request $request, $id)
    {
        $businessCategory = BusinessCategory::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:business_categories,code,' . $id,
            'name' => 'required|string|max:255|unique:business_categories,name,' . $id,
        ]);

        $businessCategory->update([
            'code' => trim((string) $validated['code']),
            'name' => trim((string) $validated['name']),
        ]);

        return back()->with('success', 'Business Category berhasil diperbarui.');
    }

    public function destroyBusinessCategory($id)
    {
        $businessCategory = BusinessCategory::findOrFail($id);
        $businessCategory->delete();

        return back()->with('success', 'Business Category berhasil dihapus.');
    }

    public function plants()
    {
        $plants = Plant::orderBy('code')->orderBy('name')->get();
        return view('database.plants', compact('plants'));
    }

    public function storePlant(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:plants,code',
            'name' => 'required|string|max:255',
        ]);

        Plant::create([
            'code' => trim((string) $validated['code']),
            'name' => trim((string) $validated['name']),
        ]);

        return back()->with('success', 'Plant berhasil ditambahkan.');
    }

    public function updatePlant(Request $request, $id)
    {
        $plant = Plant::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:plants,code,' . $id,
            'name' => 'required|string|max:255',
        ]);

        $plant->update([
            'code' => trim((string) $validated['code']),
            'name' => trim((string) $validated['name']),
        ]);

        return back()->with('success', 'Plant berhasil diperbarui.');
    }

    public function destroyPlant($id)
    {
        $plant = Plant::findOrFail($id);
        $plant->delete();

        return back()->with('success', 'Plant berhasil dihapus.');
    }

    public function pics()
    {
        $pics = Pic::orderBy('type')->orderBy('name')->get();
        return view('database.pics', compact('pics'));
    }

    public function wires()
    {
        $wires = Wire::orderBy('idcode')->get();
        $wireRates = WireRate::orderBy('period_month', 'asc')->orderBy('id', 'asc')->get();
        $periodRates = $wireRates->filter(fn ($rate) => !is_null($rate->period_month))->values();
        
        // Get active rate by ID from session or default to latest record
        $selectedRateId = (int) session('wire_selected_rate_id', 0);
        if ($selectedRateId <= 0 && $wireRates->isNotEmpty()) {
            $selectedRateId = (int) $wireRates->last()->id;
        }
        
        $activeRate = $wireRates->firstWhere('id', $selectedRateId);
        if (!$activeRate) {
            $activeRate = $wireRates->last();
            $selectedRateId = (int) ($activeRate?->id ?? 0);
        }

        $wirePriceNotes = [];
        foreach ($wires as $wire) {
            $wirePriceNotes[$wire->id] = $this->buildWirePriceNote($wire, $activeRate);
        }
        
        return view('database.wires', compact('wires', 'wireRates', 'periodRates', 'selectedRateId', 'activeRate', 'wirePriceNotes'));
    }

    public function switchWireRateMonth(Request $request)
    {
        $rateId = (int) $request->input('rate_id', 0);
        $activeRate = WireRate::find($rateId);

        if (!$activeRate) {
            return back()->with('error', 'Rate aktif yang dipilih tidak ditemukan.');
        }
        
        // Store in session
        session(['wire_selected_rate_id' => $rateId]);
        
        // Recalculate all wire prices with the selected rate
        $this->recalculateAllWirePrices($rateId);
        
        return back()->with('success', 'Rate aktif berhasil diubah. Harga wire telah diperbaharui.');
    }

    public function storeWireRate(Request $request)
    {
        $normalizedPeriod = null;
        try {
            $normalizedPeriod = Carbon::createFromFormat('Y-m', (string) $request->input('period_month'))->startOfMonth()->format('Y-m-d');
        } catch (\Throwable $e) {
            $normalizedPeriod = null;
        }

        if ($normalizedPeriod !== null) {
            $request->merge(['period_month' => $normalizedPeriod]);
        }

        $validated = $request->validateWithBag('wireRateCreate', [
            'period_month' => 'nullable|date|required_without:request_name|unique:wire_rates,period_month',
            'request_name' => 'nullable|string|max:255|required_without:period_month',
            'jpy_rate' => 'required|numeric|decimal:0,5|min:0',
            'usd_rate' => 'required|numeric|decimal:0,5|min:0',
            'lme_active' => 'required|numeric|decimal:0,5|min:0',
        ]);

        $lmeReference = floor(((float) $validated['lme_active']) / 100) * 100;

        WireRate::create([
            'period_month' => $validated['period_month'] ?? null,
            'request_name' => isset($validated['request_name']) ? trim((string) $validated['request_name']) : null,
            'jpy_rate' => $validated['jpy_rate'],
            'usd_rate' => $validated['usd_rate'],
            'lme_active' => $validated['lme_active'],
            'lme_reference' => $lmeReference,
        ]);

        $this->recalculateAllWirePrices();

        return back()->with('success', 'Rates wire berhasil ditambahkan.');
    }

    public function updateWireRate(Request $request, $id)
    {
        $wireRate = WireRate::findOrFail($id);

        $normalizedPeriod = null;
        try {
            $normalizedPeriod = Carbon::createFromFormat('Y-m', (string) $request->input('period_month'))->startOfMonth()->format('Y-m-d');
        } catch (\Throwable $e) {
            $normalizedPeriod = null;
        }

        if ($normalizedPeriod !== null) {
            $request->merge(['period_month' => $normalizedPeriod]);
        }

        $validated = $request->validateWithBag('wireRateEdit', [
            'period_month' => 'nullable|date|required_without:request_name|unique:wire_rates,period_month,' . $id,
            'request_name' => 'nullable|string|max:255|required_without:period_month',
            'jpy_rate' => 'required|numeric|decimal:0,5|min:0',
            'usd_rate' => 'required|numeric|decimal:0,5|min:0',
            'lme_active' => 'required|numeric|decimal:0,5|min:0',
        ]);

        $lmeReference = floor(((float) $validated['lme_active']) / 100) * 100;

        $wireRate->update([
            'period_month' => $validated['period_month'] ?? null,
            'request_name' => isset($validated['request_name']) ? trim((string) $validated['request_name']) : null,
            'jpy_rate' => $validated['jpy_rate'],
            'usd_rate' => $validated['usd_rate'],
            'lme_active' => $validated['lme_active'],
            'lme_reference' => $lmeReference,
        ]);

        $this->recalculateAllWirePrices();

        return back()->with('success', 'Rates wire berhasil diperbarui.');
    }

    public function destroyWireRate($id)
    {
        $wireRate = WireRate::findOrFail($id);
        $wireRate->delete();

        $this->recalculateAllWirePrices();

        return back()->with('success', 'Rates wire berhasil dihapus.');
    }

    public function storeWire(Request $request)
    {
        $validated = $request->validateWithBag('wireCreate', [
            'idcode' => 'required|string|max:255|unique:wires,idcode',
            'item' => 'required|string|max:255',
            'machine_maintenance' => 'required|string|max:255',
                'fix_cost' => 'nullable|numeric|decimal:0,5|min:0',
                'price' => 'nullable|numeric|decimal:0,5|min:0',
        ]);

        Wire::create([
            'idcode' => trim((string) $validated['idcode']),
            'item' => trim((string) $validated['item']),
            'machine_maintenance' => trim((string) $validated['machine_maintenance']),
            'fix_cost' => $validated['fix_cost'] ?? 0,
            'price' => 0,
        ]);

        $this->recalculateAllWirePrices();

        return back()->with('success', 'Wire berhasil ditambahkan.');
    }

    public function updateWire(Request $request, $id)
    {
        $wire = Wire::findOrFail($id);

        $validated = $request->validateWithBag('wireEdit', [
            'idcode' => 'required|string|max:255|unique:wires,idcode,' . $id,
            'item' => 'required|string|max:255',
            'machine_maintenance' => 'required|string|max:255',
            'fix_cost' => 'nullable|numeric|decimal:0,5|min:0',
            'price' => 'nullable|numeric|decimal:0,5|min:0',
        ]);

        $wire->update([
            'idcode' => trim((string) $validated['idcode']),
            'item' => trim((string) $validated['item']),
            'machine_maintenance' => trim((string) $validated['machine_maintenance']),
            'fix_cost' => $validated['fix_cost'] ?? 0,
            'price' => 0,
        ]);

        $this->recalculateAllWirePrices();

        return back()->with('success', 'Wire berhasil diperbarui.');
    }

    public function destroyWire($id)
    {
        $wire = Wire::findOrFail($id);
        $wire->delete();

        return back()->with('success', 'Wire berhasil dihapus.');
    }

    public function storePic(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:engineering,marketing',
        ]);

        Pic::create([
            'name' => trim((string) $validated['name']),
            'type' => $validated['type'],
        ]);

        return back()->with('success', 'PIC berhasil ditambahkan.');
    }

    public function updatePic(Request $request, $id)
    {
        $pic = Pic::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:engineering,marketing',
        ]);

        $pic->update([
            'name' => trim((string) $validated['name']),
            'type' => $validated['type'],
        ]);

        return back()->with('success', 'PIC berhasil diperbarui.');
    }

    public function destroyPic($id)
    {
        $pic = Pic::findOrFail($id);
        $pic->delete();

        return back()->with('success', 'PIC berhasil dihapus.');
    }

    // CRUD for Parts/Materials
    public function createPart()
    {
        return view('database.parts-form', ['material' => null]);
    }

    public function storePart(Request $request)
    {
        $validated = $request->validate([
            'plant' => 'nullable|string|max:255',
            'material_code' => 'required|string|max:255|unique:materials,material_code',
            'material_description' => 'nullable|string|max:255',
            'material_type' => 'nullable|string|max:255',
            'material_group' => 'nullable|string|max:255',
            'base_uom' => 'required|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'purchase_unit' => 'nullable|string|max:50',
            'currency' => 'required|string|max:10',
            'moq' => 'nullable|numeric|min:0',
            'cn' => 'nullable|string|max:255',
            'maker' => 'nullable|string|max:255',
            'add_cost_import_tax' => 'nullable|numeric|min:0|max:100',
            'price_update' => 'nullable|date',
            'price_before' => 'nullable|numeric|min:0',
        ]);

        Material::create($validated);

        $target = route('database.parts', absolute: false);
        session()->flash('success', 'Material berhasil ditambahkan!');

        return response('', 302, ['Location' => $target]);
    }

    public function downloadPartsTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Parts');

        $headers = [
            'plant',
            'material_code',
            'material_description',
            'material_type',
            'material_group',
            'base_uom',
            'price',
            'purchase_unit',
            'currency',
            'moq',
            'cn',
            'maker',
            'add_cost_import_tax',
            'price_update',
            'price_before',
        ];

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $example = [
            '1501',
            'ABC-123',
            'CONNECTOR SAMPLE',
            'RAW',
            'ELECTRICAL',
            'PCS',
            '12345',
            'PCS',
            'IDR',
            '1000',
            'N',
            'SUPPLIER A',
            '0',
            now()->format('Y-m-d'),
            '12000',
        ];

        foreach ($example as $index => $value) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '2', $value);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'parts_template_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, 'database-parts-template.xlsx')->deleteFileAfterSend(true);
    }

    public function importPartsExcel(Request $request)
    {
        $validated = $request->validateWithBag('importParts', [
            'import_file' => 'required|file|mimes:xlsx|max:20480',
        ], [
            'import_file.required' => 'File Excel wajib dipilih.',
            'import_file.mimes' => 'Format file harus .xlsx.',
            'import_file.max' => 'Ukuran file maksimal 20MB.',
        ]);

        try {
            $spreadsheet = IOFactory::load($validated['import_file']->getPathname());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        $highestRow = (int) $sheet->getHighestDataRow();
        $highestColIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        if ($highestRow < 2) {
            return back()->with('warning', 'File template kosong. Isi minimal satu baris data.');
        }

        // Expected template columns in order
        $expectedHeaders = [
            'plant',
            'material_code',
            'material_description',
            'material_type',
            'material_group',
            'base_uom',
            'price',
            'purchase_unit',
            'currency',
            'moq',
            'cn',
            'maker',
            'add_cost_import_tax',
            'price_update',
            'price_before',
        ];

        // Create headerMap from expected columns
        $headerMap = [];
        for ($col = 1; $col <= min($highestColIndex, count($expectedHeaders)); $col++) {
            $field = $expectedHeaders[$col - 1];
            $headerMap[$field] = $col;
        }

        $created = 0;
        $totalRows = $highestRow - 1;

        for ($row = 2; $row <= $highestRow; $row++) {
            $payload = [
                'created_at' => now(),
                'updated_at' => now(),
                'price' => 0,            // NOT NULL default
                'currency' => 'IDR',     // NOT NULL default
            ];

            // Read all expected fields from their column positions
            foreach ($expectedHeaders as $colIndex => $field) {
                $col = $colIndex + 1;
                if ($col > $highestColIndex) {
                    $payload[$field] = $field === 'base_uom' ? '' : null;
                    continue;
                }

                $cellRef = Coordinate::stringFromColumnIndex($col) . $row;
                $rawValue = trim((string) $sheet->getCell($cellRef)->getFormattedValue());

                // Process based on field type
                if (in_array($field, ['price', 'moq', 'add_cost_import_tax', 'price_before'])) {
                    // Numeric fields
                    if ($rawValue !== '') {
                        $numVal = $this->toNullableFloat($rawValue);
                        $payload[$field] = $numVal;
                    } else {
                        // For price field (NOT NULL), use default 0 if empty
                        $payload[$field] = ($field === 'price') ? 0 : null;
                    }
                } elseif ($field === 'price_update') {
                    // Date field
                    if ($rawValue !== '') {
                        try {
                            $dateVal = \Carbon\Carbon::parse($rawValue)->format('Y-m-d');
                            $payload[$field] = $dateVal;
                        } catch (\Exception $e) {
                            $payload[$field] = null;
                        }
                    } else {
                        $payload[$field] = null;
                    }
                } else {
                    // String/text fields
                    // For NOT NULL fields with defaults, don't override with null if empty
                    if ($rawValue === '') {
                        if ($field === 'base_uom') {
                            $payload[$field] = '';
                        } elseif (!in_array($field, ['currency'])) {
                            $payload[$field] = null;
                        }
                    } else {
                        $payload[$field] = $rawValue;
                    }
                }
            }

            // Raw insert to bypass all Model validation
            try {
                DB::table('materials')->insert($payload);
                $created++;
            } catch (\Throwable $e) {
                \Log::warning('Row ' . $row . ' insert failed: ' . $e->getMessage());
            }
        }

        $summary = "Import selesai. Total baris Excel: {$totalRows}, berhasil ditambahkan: {$created}.";
        return back()->with('success', $summary);
    }

    private function normalizePartsImportHeader(string $value): ?string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace(['-', ' ', '_'], '', $normalized);

        $aliases = [
            'plant' => ['plant'],
            'material_code' => ['materialcode', 'material_id_code', 'materialidcode', 'idcode', 'material', 'code'],
            'material_description' => ['materialdescription', 'description', 'materialdesc', 'desc'],
            'material_type' => ['materialtype', 'type'],
            'material_group' => ['materialgroup', 'group'],
            'base_uom' => ['baseuom', 'uom', 'unit', 'baseunit'],
            'price' => ['price', 'harga'],
            'purchase_unit' => ['purchaseunit', 'unitpurchase', 'unit'],
            'currency' => ['currency', 'curr'],
            'moq' => ['moq'],
            'cn' => ['cn', 'c_n'],
            'maker' => ['maker', 'supplier'],
            'add_cost_import_tax' => ['addcostimporttax', 'importtax', 'addcost', 'addcostpercent'],
            'price_update' => ['priceupdate', 'priceupdatedate', 'updatedate'],
            'price_before' => ['pricebefore', 'previousprice'],
        ];

        foreach ($aliases as $target => $candidateHeaders) {
            if (in_array($normalized, $candidateHeaders, true)) {
                return $target;
            }
        }

        return null;
    }

    public function destroyPartsBulk(Request $request)
    {
        $validated = $request->validate([
            'material_ids' => 'required|array|min:1',
            'material_ids.*' => 'integer|exists:materials,id',
        ], [
            'material_ids.required' => 'Pilih minimal satu material untuk dihapus.',
            'material_ids.array' => 'Format data hapus massal tidak valid.',
            'material_ids.min' => 'Pilih minimal satu material untuk dihapus.',
        ]);

        $ids = collect($validated['material_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $deleted = Material::whereIn('id', $ids)->delete();

        return back()->with('success', 'Hapus massal berhasil. Jumlah data terhapus: ' . $deleted . '.');
    }

    public function destroyPartsAll(Request $request)
    {
        $deleted = Material::query()->delete();

        return back()->with('success', 'Semua data material berhasil dihapus. Jumlah data terhapus: ' . $deleted . '.');
    }

    public function editPart($id)
    {
        $material = Material::findOrFail($id);
        return view('database.parts-form', compact('material'));
    }

    public function updatePart(Request $request, $id)
    {
        $material = Material::findOrFail($id);

        $validated = $request->validate([
            'plant' => 'nullable|string|max:255',
            'material_code' => 'required|string|max:255|unique:materials,material_code,' . $id,
            'material_description' => 'nullable|string|max:255',
            'material_type' => 'nullable|string|max:255',
            'material_group' => 'nullable|string|max:255',
            'base_uom' => 'required|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'purchase_unit' => 'nullable|string|max:50',
            'currency' => 'required|string|max:10',
            'moq' => 'nullable|numeric|min:0',
            'cn' => 'nullable|string|max:255',
            'maker' => 'nullable|string|max:255',
            'add_cost_import_tax' => 'nullable|numeric|min:0|max:100',
            'price_update' => 'nullable|date',
            'price_before' => 'nullable|numeric|min:0',
        ]);

        $material->update($validated);

        $target = route('database.parts', absolute: false);
        session()->flash('success', 'Material berhasil diperbarui!');

        return response('', 302, ['Location' => $target]);
    }

    public function destroyPart($id)
    {
        $material = Material::findOrFail($id);
        $material->delete();

        $target = route('database.parts', absolute: false);
        session()->flash('success', 'Material berhasil dihapus!');

        return response('', 302, ['Location' => $target]);
    }

    private function readImportCell($sheet, array $headerMap, string $field, int $row)
    {
        if (!isset($headerMap[$field])) {
            return '';
        }

        $cellRef = Coordinate::stringFromColumnIndex((int) $headerMap[$field]) . $row;
        return $sheet->getCell($cellRef)->getFormattedValue();
    }

    private function parseLocalizedDecimal($value): float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return 0.0;
        }

        $normalized = preg_replace('/[^0-9,\.\-]/', '', $raw);
        if ($normalized === '' || $normalized === '-' || $normalized === ',' || $normalized === '.') {
            return 0.0;
        }

        $hasComma = str_contains($normalized, ',');
        $hasDot = str_contains($normalized, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($normalized, ',');
            $lastDot = strrpos($normalized, '.');
            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($hasComma) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }

    private function resolveWireLookupValue(string $idCode, string $item, float $lmeReference): float
    {
        $lookupData = $this->loadWireLookupData();
        if ($lookupData === null) {
            return 0.0;
        }

        $idCode = trim($idCode);
        $item = trim($item);
        $lmeKey = (int) round($lmeReference);

        $targetColumn = $lookupData['lmeColumnByValue'][$lmeKey] ?? null;
        if (!$targetColumn) {
            return 0.0;
        }

        $row = null;
        if ($idCode !== '' && isset($lookupData['rowByKey'][$idCode])) {
            $row = $lookupData['rowByKey'][$idCode];
        }

        if ($row === null && $item !== '') {
            $normalizedItem = strtolower(preg_replace('/\s+/', '', $item));
            $row = $lookupData['rowByItem'][$normalizedItem] ?? null;
        }

        if ($row === null) {
            return 0.0;
        }

        $valueRaw = $lookupData['valueByRowCol'][$row][$targetColumn] ?? null;
        if ($valueRaw === null) {
            return 0.0;
        }

        return $this->parseLocalizedDecimal($valueRaw);
    }

    private function loadWireLookupData(): ?array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $filePath = public_path('templates/lookup wire.xlsx');
        if (!is_file($filePath)) {
            $cache = null;
            return null;
        }

        try {
            $sheet = IOFactory::load($filePath)->getActiveSheet();
        } catch (\Throwable $e) {
            $cache = null;
            return null;
        }

        $lmeColumnByValue = [];
        for ($i = Coordinate::columnIndexFromString('E'); $i <= Coordinate::columnIndexFromString('CX'); $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $headerRaw = trim((string) $sheet->getCell($col . '8')->getFormattedValue());
            if ($headerRaw === '') {
                continue;
            }

            $headerValue = (int) round($this->parseLocalizedDecimal($headerRaw));
            if ($headerValue > 0) {
                $lmeColumnByValue[$headerValue] = $col;
            }
        }

        $rowByKey = [];
        $rowByItem = [];
        $valueByRowCol = [];

        for ($row = 10; $row <= 73; $row++) {
            $key = trim((string) $sheet->getCell('C' . $row)->getFormattedValue());
            $item = trim((string) $sheet->getCell('D' . $row)->getFormattedValue());

            if ($key !== '') {
                $rowByKey[$key] = $row;
            }

            if ($item !== '') {
                $normalizedItem = strtolower(preg_replace('/\s+/', '', $item));
                $rowByItem[$normalizedItem] = $row;
            }

            foreach ($lmeColumnByValue as $col) {
                $valueByRowCol[$row][$col] = trim((string) $sheet->getCell($col . $row)->getFormattedValue());
            }
        }

        $cache = [
            'lmeColumnByValue' => $lmeColumnByValue,
            'rowByKey' => $rowByKey,
            'rowByItem' => $rowByItem,
            'valueByRowCol' => $valueByRowCol,
        ];

        return $cache;
    }

    private function calculateWirePriceValue(Wire $wire, ?WireRate $rate): float
    {
        if (!$rate) {
            return 0.0;
        }

        $usdRate = (float) ($rate->usd_rate ?? 0);
        $lmeActive = (float) ($rate->lme_active ?? 0);
        $lmeReference = (float) ($rate->lme_reference ?? 0);
        $item = trim((string) ($wire->item ?? ''));

        if ($usdRate <= 0 || $lmeActive <= 0 || $item === '') {
            return 0.0;
        }

        $lookupValue = $this->resolveWireLookupValue((string) ($wire->idcode ?? ''), $item, $lmeReference);
        if ($lookupValue <= 0) {
            return 0.0;
        }

        $machineMaintenance = $this->parseLocalizedDecimal($wire->machine_maintenance ?? 0);
        $fixCost = $this->parseLocalizedDecimal($wire->fix_cost ?? 0);
        $markupFactor = $this->wireRateMarkupFactor($rate);

        $baseValue = (($lookupValue + $machineMaintenance) * $usdRate) + $fixCost;
        $roundedValue = $this->applyWireRateRounding($baseValue, $rate);

        return round($roundedValue * $markupFactor, 2);
    }

    private function wireRateMarkupFactor(WireRate $rate): float
    {
        return 1.03;
    }

    private function applyWireRateRounding(float $baseValue, WireRate $rate): float
    {
        // Period rate uses ROUNDUP, request rate uses ROUNDDOWN.
        return $rate->period_month ? (float) ceil($baseValue) : (float) floor($baseValue);
    }

    private function buildWirePriceNote(Wire $wire, ?WireRate $rate): array
    {
        if (!$rate) {
            return [
                'status' => 'error',
                'reason' => 'Rate aktif belum tersedia.',
            ];
        }

        $usdRate = (float) ($rate->usd_rate ?? 0);
        $lmeActive = (float) ($rate->lme_active ?? 0);
        $lmeReference = (float) ($rate->lme_reference ?? 0);
        $item = trim((string) ($wire->item ?? ''));
        $machineMaintenance = $this->parseLocalizedDecimal($wire->machine_maintenance ?? 0);
        $fixCost = $this->parseLocalizedDecimal($wire->fix_cost ?? 0);
        $markupFactor = $this->wireRateMarkupFactor($rate);
        $roundingLabel = $rate->period_month ? 'ROUNDUP (ceil)' : 'ROUNDDOWN (floor)';

        $rateLabel = $rate->period_month
            ? $rate->period_month->format('M-Y')
            : (trim((string) ($rate->request_name ?? '')) !== '' ? trim((string) $rate->request_name) : 'Request Khusus');

        if ($usdRate <= 0 || $lmeActive <= 0 || $item === '') {
            return [
                'status' => 'error',
                'reason' => 'Syarat perhitungan belum terpenuhi (USD, LME aktif, atau item kosong).',
                'rate_label' => $rateLabel,
                'usd_rate' => $usdRate,
                'lme_active' => $lmeActive,
                'lme_reference' => $lmeReference,
            ];
        }

        $lookupValue = $this->resolveWireLookupValue((string) ($wire->idcode ?? ''), $item, $lmeReference);
        if ($lookupValue <= 0) {
            return [
                'status' => 'error',
                'reason' => 'Lookup value tidak ditemukan dari tabel referensi.',
                'rate_label' => $rateLabel,
                'usd_rate' => $usdRate,
                'lme_active' => $lmeActive,
                'lme_reference' => $lmeReference,
                'machine_maintenance' => $machineMaintenance,
                'fix_cost' => $fixCost,
            ];
        }

        $baseValue = (($lookupValue + $machineMaintenance) * $usdRate) + $fixCost;
        $roundedValue = $this->applyWireRateRounding($baseValue, $rate);
        $finalPrice = round($roundedValue * $markupFactor, 2);

        return [
            'status' => 'ok',
            'rate_label' => $rateLabel,
            'usd_rate' => $usdRate,
            'lme_active' => $lmeActive,
            'lme_reference' => $lmeReference,
            'lookup_value' => $lookupValue,
            'machine_maintenance' => $machineMaintenance,
            'fix_cost' => $fixCost,
            'base_value' => $baseValue,
            'rounded_value' => $roundedValue,
            'rounding_label' => $roundingLabel,
            'markup_factor' => $markupFactor,
            'final_price' => $finalPrice,
        ];
    }

    private function recalculateAllWirePrices(?int $rateId = null): void
    {
        $activeRate = null;

        if ($rateId && $rateId > 0) {
            $activeRate = WireRate::find($rateId);
        }

        if (!$activeRate) {
            $sessionRateId = (int) session('wire_selected_rate_id', 0);
            if ($sessionRateId > 0) {
                $activeRate = WireRate::find($sessionRateId);
            }
        }

        if (!$activeRate) {
            // Fallback to latest rate
            $activeRate = WireRate::query()->orderByDesc('period_month')->orderByDesc('id')->first();
        }

        Wire::query()->orderBy('id')->chunkById(100, function ($wires) use ($activeRate) {
            foreach ($wires as $wire) {
                $calculatedPrice = $this->calculateWirePriceValue($wire, $activeRate);
                $wire->update(['price' => $calculatedPrice]);
            }
        });
    }

    private function hasImportField(array $headerMap, string $field): bool
    {
        return isset($headerMap[$field]);
    }

    private function nullableTrim($value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function toNullableFloat($value): ?float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9,\.\-]/', '', $raw);
        if ($normalized === '' || $normalized === '-' || $normalized === ',' || $normalized === '.') {
            return null;
        }

        $hasComma = str_contains($normalized, ',');
        $hasDot = str_contains($normalized, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($normalized, ',');
            $lastDot = strrpos($normalized, '.');
            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($hasComma) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function parseExcelDateValue($sheet, array $headerMap, string $field, int $row): ?string
    {
        if (!isset($headerMap[$field])) {
            return null;
        }

        $col = (int) $headerMap[$field];
        $cellRef = Coordinate::stringFromColumnIndex($col) . $row;
        $cell = $sheet->getCell($cellRef);
        $raw = $cell->getValue();

        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $raw)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $parsed = strtotime((string) $raw);
        if ($parsed === false) {
            return null;
        }

        return date('Y-m-d', $parsed);
    }
}
