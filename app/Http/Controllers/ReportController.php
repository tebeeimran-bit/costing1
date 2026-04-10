<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CogmSubmission;
use App\Models\CostingData;
use App\Models\Customer;
use App\Models\ExchangeRate;
use App\Models\MaterialBreakdown;
use App\Models\Product;
use App\Models\UnpricedPart;
use App\Models\WireRate;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Resume COGM - Summary per project/customer
     */
    public function resumeCogm(Request $request)
    {
        $costings = CostingData::with(['customer', 'product', 'trackingRevision'])
            ->orderBy('customer_id')
            ->get()
            ->map(function ($item) {
                $cogm = (float) $item->material_cost + (float) $item->labor_cost + (float) $item->overhead_cost + (float) $item->scrap_cost;
                $forecast = (float) ($item->forecast ?? 0);
                $period = (float) ($item->project_period ?? 0);
                $potential = $forecast * $period * $cogm;

                $status = 'A00';
                if (($item->trackingRevision?->a05 ?? null) === 'ada') $status = 'A05';
                elseif (($item->trackingRevision?->a04 ?? null) === 'ada') $status = 'A04';

                return (object)[
                    'id' => $item->id,
                    'customer' => $item->customer->name ?? '-',
                    'model' => $item->model ?? '-',
                    'assy_name' => $item->assy_name ?? '-',
                    'assy_no' => $item->assy_no ?? '-',
                    'period' => $item->period ?? '-',
                    'material' => (float) $item->material_cost,
                    'labor' => (float) $item->labor_cost,
                    'overhead' => (float) $item->overhead_cost,
                    'scrap' => (float) $item->scrap_cost,
                    'cogm' => $cogm,
                    'forecast' => $forecast,
                    'project_period' => $period,
                    'potential' => $potential,
                    'status' => $status,
                    'line' => $item->product->line ?? $item->line ?? '-',
                ];
            });

        // Summary by customer
        $byCustomer = $costings->groupBy('customer')->map(function ($items, $name) {
            return (object)[
                'customer' => $name,
                'count' => $items->count(),
                'total_cogm' => $items->sum('cogm'),
                'total_potential' => $items->sum('potential'),
            ];
        })->values();

        $totalCogm = $costings->sum('cogm');
        $totalPotential = $costings->sum('potential');

        return view('reports.resume-cogm', compact('costings', 'byCustomer', 'totalCogm', 'totalPotential'));
    }

    /**
     * Analisis Tren - Cost trends over time
     */
    public function analisisTren()
    {
        $costings = CostingData::select('period', 'material_cost', 'labor_cost', 'overhead_cost', 'scrap_cost')
            ->whereNotNull('period')
            ->orderBy('period')
            ->get();

        $byPeriod = $costings->groupBy('period')->map(function ($items, $period) {
            return (object)[
                'period' => $period,
                'count' => $items->count(),
                'material' => $items->sum(fn($i) => (float) $i->material_cost),
                'labor' => $items->sum(fn($i) => (float) $i->labor_cost),
                'overhead' => $items->sum(fn($i) => (float) $i->overhead_cost),
                'total' => $items->sum(fn($i) => (float) $i->material_cost + (float) $i->labor_cost + (float) $i->overhead_cost + (float) $i->scrap_cost),
                'avg_cogm' => $items->avg(fn($i) => (float) $i->material_cost + (float) $i->labor_cost + (float) $i->overhead_cost + (float) $i->scrap_cost),
            ];
        })->sortKeys()->values();

        // Exchange rate trends
        $exchangeRates = ExchangeRate::orderBy('period_date')->get();

        return view('reports.analisis-tren', compact('byPeriod', 'exchangeRates'));
    }

    /**
     * Rate & Kurs management
     */
    public function rateKurs()
    {
        $exchangeRates = ExchangeRate::orderByDesc('period_date')->get();
        $wireRates = WireRate::orderByDesc('period_month')->get();

        return view('reports.rate-kurs', compact('exchangeRates', 'wireRates'));
    }

    public function storeExchangeRate(Request $request)
    {
        $request->validate([
            'period_date' => 'required|date',
            'usd_to_idr' => 'nullable|numeric',
            'jpy_to_idr' => 'nullable|numeric',
            'lme_copper' => 'nullable|numeric',
            'source' => 'nullable|string|max:100',
        ]);
        ExchangeRate::create($request->only('period_date', 'usd_to_idr', 'jpy_to_idr', 'lme_copper', 'source'));
        return back()->with('success', 'Exchange rate berhasil ditambahkan.');
    }

    public function destroyExchangeRate($id)
    {
        ExchangeRate::findOrFail($id)->delete();
        return back()->with('success', 'Exchange rate berhasil dihapus.');
    }

    /**
     * Product management
     */
    public function products()
    {
        $products = Product::withCount('costingData')->orderBy('name')->get();
        return view('reports.products', compact('products'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50', 'name' => 'required|string|max:150', 'line' => 'nullable|string|max:100']);
        Product::create($request->only('code', 'name', 'line'));
        return back()->with('success', 'Product berhasil ditambahkan.');
    }

    public function updateProduct(Request $request, $id)
    {
        $request->validate(['code' => 'required|string|max:50', 'name' => 'required|string|max:150', 'line' => 'nullable|string|max:100']);
        Product::findOrFail($id)->update($request->only('code', 'name', 'line'));
        return back()->with('success', 'Product berhasil diperbarui.');
    }

    public function destroyProduct($id)
    {
        Product::findOrFail($id)->delete();
        return back()->with('success', 'Product berhasil dihapus.');
    }

    /**
     * Material Breakdown
     */
    public function materialBreakdown(Request $request)
    {
        $search = trim($request->input('search', ''));
        $query = MaterialBreakdown::with(['costingData.customer', 'costingData.product']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                  ->orWhere('part_name', 'like', "%{$search}%")
                  ->orWhere('id_code', 'like', "%{$search}%");
            });
        }

        $breakdowns = $query->orderByDesc('id')->paginate(20)->appends($request->query());
        $totalParts = MaterialBreakdown::count();
        $uniqueParts = MaterialBreakdown::distinct('part_no')->count('part_no');

        return view('reports.material-breakdown', compact('breakdowns', 'totalParts', 'uniqueParts', 'search'));
    }

    /**
     * COGM Submission / Approval
     */
    public function cogmSubmissions()
    {
        $submissions = CogmSubmission::with(['revision.project'])
            ->orderByDesc('submitted_at')
            ->get()
            ->map(function ($sub) {
                // Find CostingData that references this revision
                $costing = CostingData::with('customer')
                    ->where('tracking_revision_id', $sub->document_revision_id)
                    ->first();
                return (object)[
                    'id' => $sub->id,
                    'customer' => $costing?->customer?->name ?? '-',
                    'model' => $costing?->model ?? '-',
                    'assy_name' => $costing?->assy_name ?? '-',
                    'cogm_value' => (float) $sub->cogm_value,
                    'submitted_by' => $sub->submitted_by ?? '-',
                    'pic_marketing' => $sub->pic_marketing ?? '-',
                    'submitted_at' => $sub->submitted_at,
                    'notes' => $sub->notes,
                    'revision_id' => $sub->document_revision_id,
                ];
            });

        $totalSubmissions = $submissions->count();
        $totalCogmValue = $submissions->sum('cogm_value');

        return view('reports.cogm-submissions', compact('submissions', 'totalSubmissions', 'totalCogmValue'));
    }

    /**
     * Laporan & Export
     */
    public function laporan()
    {
        $costingsByCustomer = CostingData::with('customer')
            ->get()
            ->groupBy(fn($item) => $item->customer->name ?? 'Unknown')
            ->map(function ($items, $name) {
                $totalCogm = $items->sum(fn($i) => (float) $i->material_cost + (float) $i->labor_cost + (float) $i->overhead_cost + (float) $i->scrap_cost);
                return (object)[
                    'customer' => $name,
                    'projects' => $items->count(),
                    'material' => $items->sum(fn($i) => (float) $i->material_cost),
                    'labor' => $items->sum(fn($i) => (float) $i->labor_cost),
                    'overhead' => $items->sum(fn($i) => (float) $i->overhead_cost),
                    'cogm' => $totalCogm,
                ];
            })->sortByDesc('cogm')->values();

        $costingsByCategory = CostingData::with('product')
            ->get()
            ->groupBy(fn($item) => $item->product->line ?? $item->line ?? 'Unknown')
            ->map(function ($items, $name) {
                $totalCogm = $items->sum(fn($i) => (float) $i->material_cost + (float) $i->labor_cost + (float) $i->overhead_cost + (float) $i->scrap_cost);
                return (object)[
                    'category' => $name,
                    'projects' => $items->count(),
                    'material' => $items->sum(fn($i) => (float) $i->material_cost),
                    'labor' => $items->sum(fn($i) => (float) $i->labor_cost),
                    'overhead' => $items->sum(fn($i) => (float) $i->overhead_cost),
                    'cogm' => $totalCogm,
                ];
            })->sortByDesc('cogm')->values();

        return view('reports.laporan', compact('costingsByCustomer', 'costingsByCategory'));
    }

    /**
     * Unpriced Parts
     */
    public function unpricedParts()
    {
        $parts = UnpricedPart::with(['costingData.customer', 'revision'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($part) {
                return (object)[
                    'id' => $part->id,
                    'part_number' => $part->part_number ?? '-',
                    'part_name' => $part->part_name ?? '-',
                    'customer' => $part->costingData?->customer?->name ?? '-',
                    'model' => $part->costingData?->model ?? '-',
                    'detected_price' => (float) $part->detected_price,
                    'manual_price' => (float) $part->manual_price,
                    'resolved_at' => $part->resolved_at,
                    'resolution_source' => $part->resolution_source ?? '-',
                    'notes' => $part->notes,
                ];
            });

        $totalParts = $parts->count();
        $resolvedParts = $parts->filter(fn($p) => $p->resolved_at !== null)->count();
        $unresolvedParts = $totalParts - $resolvedParts;

        return view('reports.unpriced-parts', compact('parts', 'totalParts', 'resolvedParts', 'unresolvedParts'));
    }

    /**
     * Audit Trail
     */
    public function auditTrail(Request $request)
    {
        $query = AuditLog::orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        $logs = $query->paginate(25)->appends($request->query());

        $actionOptions = AuditLog::distinct()->pluck('action')->sort()->values();
        $moduleOptions = AuditLog::distinct()->pluck('module')->sort()->values();

        return view('reports.audit-trail', compact('logs', 'actionOptions', 'moduleOptions'));
    }
}
