<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Material;
use App\Models\CostingData;
use App\Models\CycleTimeTemplate;
use App\Models\MaterialBreakdown;
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
        $customers = Customer::all();
        $materials = Material::all();
        $cycleTimeTemplates = CycleTimeTemplate::orderBy('id')->get();
        $lines = Product::distinct('line')->pluck('line');
        $periods = CostingData::distinct('period')->orderBy('period', 'desc')->pluck('period');

        // Get existing costing data if editing
        $costingDataId = $request->get('id');
        $costingData = null;
        $materialBreakdowns = collect();

        if ($costingDataId) {
            $costingData = CostingData::with('materialBreakdowns.material')->find($costingDataId);
            if ($costingData) {
                $materialBreakdowns = $costingData->materialBreakdowns;
            }
        }

        return view('form', compact(
            'products',
            'customers',
            'materials',
            'cycleTimeTemplates',
            'lines',
            'periods',
            'costingData',
            'materialBreakdowns'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
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
            'overhead_cost' => 'required|numeric',
            'scrap_cost' => 'required|numeric',
            'revenue' => 'nullable|numeric',
            'qty_good' => 'nullable|integer',
            'materials' => 'nullable|array',
            'materials.*.part_no' => 'nullable|string',
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
            $costingData = CostingData::create($request->except('materials'));

            if ($request->has('materials')) {
                foreach ($request->materials as $matData) {
                    if (empty($matData['part_no']))
                        continue;

                    // Find or create Material
                    $material = Material::firstOrCreate(
                        ['part_no' => $matData['part_no']],
                        [
                            'id_code' => $matData['id_code'] ?? null,
                            'part_name' => $matData['part_name'] ?? null,
                            'unit' => $matData['unit'] ?? 'PCS',
                            'pro_code' => $matData['pro_code'] ?? null,
                            'supplier_name' => $matData['supplier'] ?? null,
                        ]
                    );

                    // Re-calculate logic (replicating JS logic for safety)
                    $unit = strtoupper($matData['unit'] ?? 'PCS');
                    $qtyReq = floatval($matData['qty_req'] ?? 0);
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
                }
            }

            DB::commit();
                return redirect(route('form', absolute: false))->with('success', 'Data costing berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
