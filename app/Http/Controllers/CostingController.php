<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Material;
use App\Models\CostingData;
use App\Models\UnpricedPart;
use App\Models\DocumentRevision;
use App\Models\CycleTimeTemplate;
use App\Models\MaterialBreakdown;
use App\Models\Plant;
use App\Models\BusinessCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CostingController extends Controller
{
    public function dashboard(Request $request)
    {
        $period = $request->get('period', '2025-01');
        $line = $request->get('line', 'all');
        $productFilter = $request->get('product', 'all');

        // Get all products and customers for filters
        $products = Product::all();
        $customers = Customer::all();
        $lines = Product::distinct('line')->pluck('line');

        // Get costing data for the period
        $query = CostingData::with(['product', 'customer'])
            ->where('period', $period);

        if ($line !== 'all') {
            $query->whereHas('product', function ($q) use ($line) {
                $q->where('line', $line);
            });
        }

        if ($productFilter !== 'all') {
            $query->where('product_id', $productFilter);
        }

        $costingData = $query->get();

        // Calculate KPIs
        $totalCost = $costingData->sum('total_cost');
        $totalQty = $costingData->sum('qty_good');
        $avgCostPerUnit = $totalQty > 0 ? $totalCost / $totalQty : 0;

        // Find highest cost per unit product
        $highestCostProduct = $costingData->sortByDesc('cost_per_unit')->first();

        // Get cost per product for bar chart
        $costPerProduct = $costingData->map(function ($item) {
            return [
                'name' => $item->product->name,
                'cost_per_unit' => $item->cost_per_unit,
                'material_cost' => $item->material_cost,
                'labor_cost' => $item->labor_cost,
                'overhead_cost' => $item->overhead_cost,
            ];
        })->sortByDesc('cost_per_unit')->values();

        // Get max cost for chart scaling
        $maxCostPerUnit = $costPerProduct->max('cost_per_unit') ?: 1;

        // Get trend data (last 6 months)
        $trendData = CostingData::where('product_id', 1)
            ->orderBy('period')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => $item->period,
                    'cost_per_unit' => $item->cost_per_unit,
                ];
            });

        // Get top 5 customers by revenue
        $topCustomers = CostingData::with('customer')
            ->where('period', $period)
            ->get()
            ->groupBy('customer_id')
            ->map(function ($items) {
                return [
                    'name' => $items->first()->customer->name,
                    'revenue' => $items->sum('revenue'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        $maxRevenue = $topCustomers->max('revenue') ?: 1;

        // Material breakdown summary
        $materialBreakdown = $costingData->map(function ($item) {
            $total = $item->material_cost + $item->labor_cost + $item->overhead_cost;
            return [
                'name' => $item->product->name,
                'material_pct' => $total > 0 ? ($item->material_cost / $total) * 100 : 0,
                'labor_pct' => $total > 0 ? ($item->labor_cost / $total) * 100 : 0,
                'overhead_pct' => $total > 0 ? ($item->overhead_cost / $total) * 100 : 0,
            ];
        });

        // Available periods
        $periods = CostingData::distinct('period')->orderBy('period', 'desc')->pluck('period');

        return view('dashboard', compact(
            'period',
            'line',
            'productFilter',
            'products',
            'customers',
            'lines',
            'costingData',
            'totalCost',
            'totalQty',
            'avgCostPerUnit',
            'highestCostProduct',
            'costPerProduct',
            'maxCostPerUnit',
            'trendData',
            'topCustomers',
            'maxRevenue',
            'materialBreakdown',
            'periods'
        ));
    }

    public function form(Request $request)
    {
        $products = Product::all();
        $businessCategories = BusinessCategory::orderBy('code')->orderBy('name')->get();
        $customers = Customer::all();
        $materials = Material::all();
        $cycleTimeTemplates = CycleTimeTemplate::orderBy('id')->get();
        $plants = Plant::orderBy('code')->orderBy('name')->get();
        $periods = CostingData::distinct('period')->orderBy('period', 'desc')->pluck('period');

        // Get existing costing data if editing
        $costingDataId = $request->get('id');
        $costingData = null;
        $materialBreakdowns = collect();
        $trackingRevision = null;
        $openUnpricedParts = collect();
        $trackingRevisionId = $request->get('tracking_revision_id');
        $trackingProjectPrefill = [
            'business_category_id' => null,
            'customer_id' => null,
            'model' => null,
            'assy_no' => null,
            'assy_name' => null,
        ];

        if ($trackingRevisionId) {
            $trackingRevision = DocumentRevision::with('project')->find($trackingRevisionId);

            if ($trackingRevision) {
                $openUnpricedParts = UnpricedPart::where('document_revision_id', $trackingRevision->id)
                    ->whereNull('resolved_at')
                    ->orderBy('part_number')
                    ->get();

                $project = $trackingRevision->project;
                if ($project) {
                    $normalize = fn (?string $value): string => preg_replace('/[^a-z0-9]/', '', Str::lower((string) $value));
                    $trackingCustomer = $normalize($project->customer);
                    $trackingModel = $normalize($project->model);
                    $trackingPartNumber = $normalize($project->part_number);
                    $trackingPartName = $normalize($project->part_name);

                    $matchedCustomer = $customers->first(function ($customer) use ($normalize, $trackingCustomer) {
                        if ($trackingCustomer === '') {
                            return false;
                        }

                        $nameNorm = $normalize($customer->name);
                        $codeNorm = $normalize($customer->code ?? '');

                        return $nameNorm === $trackingCustomer
                            || $codeNorm === $trackingCustomer
                            || ($nameNorm !== '' && str_contains($nameNorm, $trackingCustomer))
                            || ($nameNorm !== '' && str_contains($trackingCustomer, $nameNorm));
                    });

                    $matchedProduct = $project->product_id
                        ? $products->firstWhere('id', (int) $project->product_id)
                        : null;

                    $matchedBusinessCategory = null;
                    if ($matchedProduct) {
                        $productCodeNorm = $normalize($matchedProduct->code ?? '');
                        $productNameNorm = $normalize($matchedProduct->name ?? '');

                        $matchedBusinessCategory = $businessCategories->first(function ($category) use ($normalize, $productCodeNorm, $productNameNorm) {
                            $categoryCodeNorm = $normalize($category->code ?? '');
                            $categoryNameNorm = $normalize($category->name ?? '');

                            return ($productCodeNorm !== '' && $categoryCodeNorm === $productCodeNorm)
                                || ($productNameNorm !== '' && $categoryNameNorm === $productNameNorm);
                        });
                    }

                    if (!$matchedProduct) {
                        $matchedProduct = $products->first(function ($product) use (
                            $normalize,
                            $trackingModel,
                            $trackingPartNumber,
                            $trackingPartName
                        ) {
                            $productCode = $normalize($product->code ?? '');
                            $productName = $normalize($product->name ?? '');

                            $needles = array_filter([$trackingModel, $trackingPartNumber, $trackingPartName]);
                            if (empty($needles)) {
                                return false;
                            }

                            foreach ($needles as $needle) {
                                if ($needle === '') {
                                    continue;
                                }

                                if ($productCode === $needle || $productName === $needle) {
                                    return true;
                                }

                                if (($productCode !== '' && str_contains($productCode, $needle))
                                    || ($productName !== '' && str_contains($productName, $needle))
                                    || ($productCode !== '' && str_contains($needle, $productCode))
                                    || ($productName !== '' && str_contains($needle, $productName))) {
                                    return true;
                                }
                            }

                            return false;
                        });
                    }

                    $trackingProjectPrefill = [
                        'business_category_id' => $matchedBusinessCategory?->id,
                        'customer_id' => $matchedCustomer?->id,
                        'model' => $project->model,
                        'assy_no' => $project->part_number,
                        'assy_name' => $project->part_name,
                    ];
                }
            }
        }

        if ($costingDataId) {
            $costingData = CostingData::with('materialBreakdowns.material')->find($costingDataId);
            if ($costingData) {
                $materialBreakdowns = $costingData->materialBreakdowns;
            }
        }

        return view('form', compact(
            'products',
            'businessCategories',
            'customers',
            'materials',
            'cycleTimeTemplates',
            'plants',
            'periods',
            'costingData',
            'materialBreakdowns',
            'trackingRevision',
            'trackingRevisionId',
            'openUnpricedParts',
            'trackingProjectPrefill'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_category_id' => 'required|exists:business_categories,id',
            'customer_id' => 'required|exists:customers,id',
            'tracking_revision_id' => 'nullable|exists:document_revisions,id',
            'period' => 'required|string',
            'line' => 'nullable|string',
            'model' => 'nullable|string',
            'assy_no' => 'nullable|string',
            'assy_name' => 'nullable|string',
            'exchange_rate_usd' => 'required|numeric',
            'exchange_rate_jpy' => 'required|numeric',
            'lme_rate' => 'nullable|numeric',
            'forecast' => 'required|integer',
            'project_period' => 'required|integer',
            'material_cost' => 'required|numeric',
            'labor_cost' => 'required|numeric',
            'overhead_cost' => 'nullable|numeric',
            'scrap_cost' => 'nullable|numeric',
            'revenue' => 'nullable|numeric',
            'qty_good' => 'nullable|integer',
            'materials' => 'nullable|array',
            'materials.*.part_no' => 'nullable|string',
            'materials.*.part_name' => 'nullable|string',
            'materials.*.qty_req' => 'nullable|numeric',
            'materials.*.unit' => 'nullable|string',
            'materials.*.amount1' => 'nullable|numeric',
            'materials.*.unit_price_basis' => 'nullable|numeric',
            'materials.*.qty_moq' => 'nullable|numeric',
            'materials.*.cn_type' => 'nullable|string',
            'materials.*.import_tax' => 'nullable|numeric',
            'manual_unpriced_prices' => 'nullable|array',
            'cycle_times' => 'nullable|array',
            'cycle_times.*.process' => 'nullable|string',
            'cycle_times.*.qty' => 'nullable|numeric',
            'cycle_times.*.time_hour' => 'nullable|numeric',
            'cycle_times.*.time_sec' => 'nullable|numeric',
            'cycle_times.*.time_sec_per_qty' => 'nullable|numeric',
            'cycle_times.*.cost_per_sec' => 'nullable|numeric',
            'cycle_times.*.cost_per_unit' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $businessCategory = BusinessCategory::findOrFail((int) $validated['business_category_id']);
            $product = Product::firstOrCreate(
                ['code' => trim((string) $businessCategory->code)],
                ['name' => trim((string) $businessCategory->name)]
            );

            if (trim((string) $product->name) !== trim((string) $businessCategory->name)) {
                $product->update(['name' => trim((string) $businessCategory->name)]);
            }

            $payload = $request->except(['materials', 'business_category_id']);
            $payload['product_id'] = $product->id;
            $payload['overhead_cost'] = $validated['overhead_cost'] ?? 0;
            $payload['scrap_cost'] = $validated['scrap_cost'] ?? 0;

            $costingData = CostingData::create($payload);
            $partAggregation = [];
            $manualUnpricedPrices = collect($request->input('manual_unpriced_prices', []))
                ->mapWithKeys(function ($value, $key) {
                    return [strtolower(trim((string) $key)) => floatval($value)];
                });

            if ($request->has('materials')) {
                foreach ($request->materials as $matData) {
                    if (empty($matData['part_no']))
                        continue;

                    $partNumber = trim((string) ($matData['part_no'] ?? ''));
                    $partKey = strtolower($partNumber);
                    $partName = trim((string) ($matData['part_name'] ?? ''));
                    $qtyReq = floatval($matData['qty_req'] ?? 0);
                    $manualPrice = floatval($manualUnpricedPrices->get($partKey, 0));

                    // Find or create Material
                    $material = Material::firstOrCreate(
                        ['material_code' => $partNumber],
                        [
                            'material_description' => $partName ?: null,
                            'base_uom' => $matData['unit'] ?? 'PCS',
                            'maker' => $matData['supplier'] ?? null,
                            'currency' => $matData['currency'] ?? 'IDR',
                            'price' => 0,
                        ]
                    );

                    if ($manualPrice > 0) {
                        $material->price = $manualPrice;
                        $material->price_update = now()->toDateString();
                        $material->save();
                    }

                    // Re-calculate logic (replicating JS logic for safety)
                    $unit = strtoupper($matData['unit'] ?? 'PCS');
                    $moq = floatval($matData['qty_moq'] ?? 0);
                    $forecast = $request->forecast;
                    $periodYear = $request->project_period;

                    // Multiply Factor Logic
                    $unitDivisor = ($unit === 'MM') ? 1000 : 1;
                    $denominator = $forecast * $periodYear * 12 * $qtyReq;
                    $denominator = ($denominator != 0) ? ($denominator / $unitDivisor) : 0;

                    $ratio = ($denominator != 0) ? ($moq / $denominator) : 0;
                    $cnType = $matData['cn_type'] ?? 'N';

                    $multiplyFactor = ($cnType === 'C' || $ratio < 1) ? 1 : $ratio;

                    // Amount 2 Logic
                    $priceBase = floatval($matData['amount1'] ?? 0);
                    $importTax = floatval($matData['import_tax'] ?? 0);

                    $extra = $priceBase * ($importTax / 100);
                    $base = $priceBase + $extra;
                    $numerator = $multiplyFactor * $base;

                    $unitDivisor2 = in_array(strtoupper($unit), ['METER', 'M', 'MTR', 'MM']) ? 1000 : 1;
                    $amount2 = ($unitDivisor2 != 0) ? ($numerator / $unitDivisor2) : 0;

                    MaterialBreakdown::create([
                        'costing_data_id' => $costingData->id,
                        'material_id' => $material->id,
                        'qty_req' => $qtyReq,
                        'amount1' => $priceBase,
                        'unit_price_basis' => floatval($matData['unit_price_basis'] ?? 0),
                        'currency' => $matData['currency'] ?? 'IDR',
                        'qty_moq' => $moq,
                        'cn_type' => $cnType,
                        'import_tax_percent' => $importTax,
                        'amount2' => $amount2,
                        'currency2' => $matData['currency'] ?? 'IDR',
                        'unit_price2' => $amount2, // Saving calculated amount2 as unit_price2 default
                    ]);

                    $rowInputPrice = floatval($matData['unit_price_basis'] ?? 0);
                    $detectedPrice = floatval($material->price ?? 0);
                    $isUnpriced = ($detectedPrice <= 0) && ($rowInputPrice <= 0) && ($manualPrice <= 0);

                    if (!isset($partAggregation[$partKey])) {
                        $partAggregation[$partKey] = [
                            'part_number' => $partNumber,
                            'part_name' => $partName,
                            'qty' => 0,
                            'detected_price' => $detectedPrice,
                            'manual_price' => $manualPrice > 0 ? $manualPrice : null,
                            'is_unpriced' => false,
                        ];
                    }

                    $partAggregation[$partKey]['qty'] += $qtyReq;
                    $partAggregation[$partKey]['is_unpriced'] = $partAggregation[$partKey]['is_unpriced'] || $isUnpriced;

                    if ($manualPrice > 0) {
                        $partAggregation[$partKey]['manual_price'] = $manualPrice;
                    }
                }
            }

            $trackingRevisionId = $validated['tracking_revision_id'] ?? null;
            if ($trackingRevisionId) {
                $trackedPartKeys = collect($partAggregation)->keys();

                $openItems = UnpricedPart::where('document_revision_id', $trackingRevisionId)
                    ->whereNull('resolved_at')
                    ->get()
                    ->keyBy(fn ($item) => strtolower($item->part_number));

                foreach ($partAggregation as $partKey => $partInfo) {
                    if ($partInfo['is_unpriced']) {
                        UnpricedPart::updateOrCreate(
                            [
                                'document_revision_id' => $trackingRevisionId,
                                'part_number' => $partInfo['part_number'],
                                'resolved_at' => null,
                            ],
                            [
                                'costing_data_id' => $costingData->id,
                                'part_name' => $partInfo['part_name'] ?: null,
                                'qty' => $partInfo['qty'],
                                'detected_price' => $partInfo['detected_price'],
                                'manual_price' => null,
                                'notes' => 'Auto-detected from Form Input validation.',
                            ]
                        );
                    } else {
                        $existingOpen = $openItems->get($partKey);
                        if ($existingOpen) {
                            $existingOpen->update([
                                'costing_data_id' => $costingData->id,
                                'manual_price' => $partInfo['manual_price'],
                                'resolved_at' => now(),
                                'resolution_source' => 'manual_or_master_price',
                            ]);
                        }
                    }
                }

                foreach ($openItems as $partKey => $openItem) {
                    if (!$trackedPartKeys->contains($partKey)) {
                        $openItem->update([
                            'costing_data_id' => $costingData->id,
                            'resolved_at' => now(),
                            'resolution_source' => 'part_removed_in_current_processing',
                        ]);
                    }
                }

                $remainingUnpriced = UnpricedPart::where('document_revision_id', $trackingRevisionId)
                    ->whereNull('resolved_at')
                    ->count();

                $statusPayload = $remainingUnpriced > 0
                    ? ['status' => DocumentRevision::STATUS_PENDING_PRICING]
                    : ['status' => DocumentRevision::STATUS_COGM_GENERATED, 'cogm_generated_at' => now()];

                DocumentRevision::whereKey($trackingRevisionId)->update($statusPayload);
            }

            DB::commit();
            $redirectUrl = route('form',
                $trackingRevisionId ? ['tracking_revision_id' => $trackingRevisionId] : [],
                false
            );
            session()->flash('success', 'Data costing berhasil disimpan!');

            return response('', 302, ['Location' => $redirectUrl]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
