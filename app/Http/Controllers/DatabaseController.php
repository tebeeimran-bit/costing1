<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Plant;
use App\Models\Pic;
use App\Models\Customer;
use App\Models\Material;
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

        $sheet = $spreadsheet->getActiveSheet();
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
