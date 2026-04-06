<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\CogmSubmission;
use App\Models\Material;
use App\Models\CostingData;
use App\Models\UnpricedPart;
use App\Models\DocumentRevision;
use App\Models\CycleTimeTemplate;
use App\Models\MaterialBreakdown;
use App\Models\Plant;
use App\Models\BusinessCategory;
use App\Models\Wire;
use App\Models\WireRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class CostingController extends Controller
{
    public function dashboard(Request $request)
    {
        $periods = CostingData::query()
            ->select('period')
            ->distinct()
            ->orderBy('period', 'desc')
            ->pluck('period')
            ->values();

        $requestedPeriod = trim((string) $request->get('period', ''));
        $period = $requestedPeriod !== '' ? $requestedPeriod : ((string) ($periods->first() ?? now()->format('Y-m')));

        if ($period !== 'all' && $periods->isNotEmpty() && !$periods->contains($period)) {
            $period = (string) $periods->first();
        }

        $businessCategoryFilter = trim((string) $request->get('business_category', 'all'));
        $customerFilter = trim((string) $request->get('customer', 'all'));
        $modelFilter = trim((string) $request->get('model', 'all'));

        $applyFilters = function ($query) use ($businessCategoryFilter, $customerFilter, $modelFilter) {
            if ($businessCategoryFilter !== '' && $businessCategoryFilter !== 'all') {
                $query->whereHas('product', function ($productQuery) use ($businessCategoryFilter) {
                    $productQuery->where('line', $businessCategoryFilter);
                });
            }

            if ($customerFilter !== '' && $customerFilter !== 'all') {
                $query->where('customer_id', (int) $customerFilter);
            }

            if ($modelFilter !== '' && $modelFilter !== 'all') {
                $query->where('model', $modelFilter);
            }

            return $query;
        };

        $resolveUnitQty = function ($item) {
            $qtyGood = (float) ($item->qty_good ?? 0);
            if ($qtyGood > 0) {
                return $qtyGood;
            }

            $forecast = (float) ($item->forecast ?? 0);
            if ($forecast > 0) {
                return $forecast;
            }

            return 0.0;
        };

        $resolvePotentialSales = function ($item) {
            $qtyPerMonth = (float) ($item->forecast ?? 0);
            $productLifeYears = (float) ($item->project_period ?? 0);
            $cogm = (float) ($item->material_cost ?? 0)
                + (float) ($item->labor_cost ?? 0)
                + (float) ($item->overhead_cost ?? 0)
                + (float) ($item->scrap_cost ?? 0);

            return $qtyPerMonth * $productLifeYears * $cogm;
        };

        $resolveAssyLabel = function ($item) {
            $candidates = [
                $item->assy_name ?? null,
                $item->assy_no ?? null,
                $item->model ?? null,
                $item->product->name ?? null,
            ];

            foreach ($candidates as $candidate) {
                $label = preg_replace('/\s+/u', ' ', (string) $candidate);
                $label = trim((string) $label);
                if ($label !== '') {
                    return $label;
                }
            }

            return 'Costing #' . (string) ($item->id ?? '-');
        };

        $resolveBusinessCategoryLabel = function ($item) {
            $line = trim((string) ($item->product->line ?? ''));
            if ($line !== '') {
                return $line;
            }

            $productName = trim((string) ($item->product->name ?? ''));
            return $productName !== '' ? $productName : 'Uncategorized';
        };

        // Get business category filter options from product line values used by costing records.
        $businessCategories = CostingData::query()
            ->join('products', 'products.id', '=', 'costing_data.product_id')
            ->whereNotNull('products.line')
            ->where('products.line', '!=', '')
            ->select('products.line')
            ->distinct()
            ->orderBy('products.line')
            ->pluck('products.line')
            ->map(function ($line) {
                return (object) [
                    'id' => (string) $line,
                    'name' => (string) $line,
                    'code' => null,
                ];
            })
            ->values();

        $customers = Customer::query()
            ->whereIn('id', CostingData::query()->select('customer_id')->distinct())
            ->orderBy('name')
            ->get();

        $selectedCustomerName = null;
        if ($customerFilter !== '' && $customerFilter !== 'all') {
            $selectedCustomerName = trim((string) optional($customers->firstWhere('id', (int) $customerFilter))->name);
            if ($selectedCustomerName === '') {
                $selectedCustomerName = null;
            }
        }

        $periodDisplayLabel = $period;
        if ($period === 'all') {
            $periodDisplayLabel = 'Semua Periode';
        } elseif (preg_match('/^\d{4}-\d{2}$/', (string) $period) === 1) {
            $periodDisplayLabel = \Carbon\Carbon::createFromFormat('Y-m', (string) $period)->format('M Y');
        }

        $periodStart = null;
        $periodEnd = null;
        if (preg_match('/^\d{4}-\d{2}$/', (string) $period) === 1) {
            $periodStart = \Carbon\Carbon::createFromFormat('Y-m', (string) $period)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();
        }

        $applyProjectFilters = function ($query) use ($businessCategoryFilter, $modelFilter, $selectedCustomerName) {
            if ($businessCategoryFilter !== '' && $businessCategoryFilter !== 'all') {
                $query->whereHas('product', function ($productQuery) use ($businessCategoryFilter) {
                    $productQuery->where('line', $businessCategoryFilter);
                });
            }

            if ($modelFilter !== '' && $modelFilter !== 'all') {
                $query->where('model', $modelFilter);
            }

            if ($selectedCustomerName !== null) {
                $query->whereRaw('LOWER(customer) = ?', [Str::lower($selectedCustomerName)]);
            }

            return $query;
        };

        $models = CostingData::query()
            ->select('model')
            ->whereNotNull('model')
            ->where('model', '!=', '')
            ->distinct()
            ->orderBy('model')
            ->pluck('model')
            ->values();

        $projectStatusScope = DocumentRevision::query();
        if ($periodStart && $periodEnd) {
            $projectStatusScope->whereBetween('received_date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ]);
        }
        $projectStatusScope->whereHas('project', function ($projectQuery) use ($applyProjectFilters) {
            $applyProjectFilters($projectQuery);
        });

        $a00ProjectCount = (clone $projectStatusScope)
            ->where('a00', 'ada')
            ->distinct('document_project_id')
            ->count('document_project_id');

        $a04ProjectCount = (clone $projectStatusScope)
            ->where('a04', 'ada')
            ->distinct('document_project_id')
            ->count('document_project_id');

        $a05ProjectCount = (clone $projectStatusScope)
            ->where('a05', 'ada')
            ->distinct('document_project_id')
            ->count('document_project_id');

        $totalProjectCount = $a00ProjectCount + $a04ProjectCount + $a05ProjectCount;

        $statusProjectData = collect([
            [
                'label' => 'A00 (RFQ/RFI)',
                'count' => $a00ProjectCount,
                'color' => '#3b82f6',
            ],
            [
                'label' => 'A04 (Canceled/Failed)',
                'count' => $a04ProjectCount,
                'color' => '#f97316',
            ],
            [
                'label' => 'A05 (Die Go/Berhasil)',
                'count' => $a05ProjectCount,
                'color' => '#22c55e',
            ],
        ]);

        $statusProjectTotal = (int) $statusProjectData->sum('count');
        $statusProjectData = $statusProjectData->map(function ($item) use ($statusProjectTotal) {
            $percentage = $statusProjectTotal > 0
                ? (((int) $item['count'] / $statusProjectTotal) * 100)
                : 0;

            return [
                'label' => $item['label'],
                'count' => (int) $item['count'],
                'percentage' => round($percentage, 1),
                'color' => $item['color'],
            ];
        })->values();

        $pieSegments = [];
        $pieStartAngle = 0.0;
        foreach ($statusProjectData as $statusItem) {
            $count = (int) ($statusItem['count'] ?? 0);
            if ($count <= 0 || $statusProjectTotal <= 0) {
                continue;
            }

            $sliceAngle = ($count / $statusProjectTotal) * 360;
            $pieEndAngle = $pieStartAngle + $sliceAngle;
            $pieSegments[] = $statusItem['color']
                . ' '
                . number_format($pieStartAngle, 2, '.', '')
                . 'deg '
                . number_format($pieEndAngle, 2, '.', '')
                . 'deg';
            $pieStartAngle = $pieEndAngle;
        }

        if (empty($pieSegments)) {
            $statusProjectPieGradient = 'conic-gradient(#e2e8f0 0deg 360deg)';
        } else {
            if ($pieStartAngle < 360) {
                $pieSegments[] = '#e2e8f0 '
                    . number_format($pieStartAngle, 2, '.', '')
                    . 'deg 360deg';
            }
            $statusProjectPieGradient = 'conic-gradient(' . implode(', ', $pieSegments) . ')';
        }

        $submitScope = CogmSubmission::query()
            ->whereNotNull('submitted_at')
            ->whereHas('revision.project', function ($projectQuery) use ($applyProjectFilters) {
                $applyProjectFilters($projectQuery);
            });

        $totalSubmitCostingMonthly = 0;
        if ($periodStart && $periodEnd) {
            $totalSubmitCostingMonthly = (clone $submitScope)
                ->whereBetween('submitted_at', [$periodStart, $periodEnd])
                ->count();
        }

        $submitAnchorPeriod = $periodStart ? $periodStart->copy() : now()->startOfMonth();
        $submitPeriodCandidates = collect(range(5, 0))
            ->map(function ($offset) use ($submitAnchorPeriod) {
                return $submitAnchorPeriod->copy()->subMonths($offset)->format('Y-m');
            })
            ->values();

        $monthlySubmitCounts = $submitPeriodCandidates->map(function ($submitPeriod) use ($submitScope) {
            $monthStart = \Carbon\Carbon::createFromFormat('Y-m', (string) $submitPeriod)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $count = (clone $submitScope)
                ->whereBetween('submitted_at', [$monthStart, $monthEnd])
                ->count();

            return [
                'period' => $submitPeriod,
                'period_label' => $monthStart->format('M y'),
                'count' => $count,
            ];
        })->values();

        $maxMonthlySubmitCount = $monthlySubmitCounts->max('count') ?: 1;

        // Get costing data for selected period.
        $query = CostingData::with(['product', 'customer', 'trackingRevision']);

        if ($period !== 'all') {
            $query->where('period', $period);
        }

        $applyFilters($query);

        $costingData = $query->get();

        // Calculate KPIs
        $totalCost = $costingData->sum('total_cost');
        $totalQty = $costingData->sum(function ($item) use ($resolveUnitQty) {
            return $resolveUnitQty($item);
        });
        $estimatedQtyProduksi = $costingData->sum(function ($item) {
            return (float) ($item->forecast ?? 0) * (float) ($item->project_period ?? 0);
        });
        $avgCostPerUnit = $totalQty > 0 ? $totalCost / $totalQty : 0;

        // Status KPI must follow filtered costing rows so total matches actual data count.
        $statusProjectCountsByLabel = [
            'A00 (RFQ/RFI)' => 0,
            'A04 (Canceled/Failed)' => 0,
            'A05 (Die Go/Berhasil)' => 0,
        ];
        $statusPotentialCostByLabel = [
            'A00 (RFQ/RFI)' => 0,
            'A04 (Canceled/Failed)' => 0,
            'A05 (Die Go/Berhasil)' => 0,
        ];

        foreach ($costingData as $item) {
            $revision = $item->trackingRevision;
            if (!$revision) {
                continue;
            }

            $potentialCost = $resolvePotentialSales($item);
            if (($revision->a05 ?? null) === 'ada') {
                $statusProjectCountsByLabel['A05 (Die Go/Berhasil)'] += 1;
                $statusPotentialCostByLabel['A05 (Die Go/Berhasil)'] += $potentialCost;
            } elseif (($revision->a04 ?? null) === 'ada') {
                $statusProjectCountsByLabel['A04 (Canceled/Failed)'] += 1;
                $statusPotentialCostByLabel['A04 (Canceled/Failed)'] += $potentialCost;
            } elseif (($revision->a00 ?? null) === 'ada') {
                $statusProjectCountsByLabel['A00 (RFQ/RFI)'] += 1;
                $statusPotentialCostByLabel['A00 (RFQ/RFI)'] += $potentialCost;
            }
        }

        $a00ProjectCount = (int) ($statusProjectCountsByLabel['A00 (RFQ/RFI)'] ?? 0);
        $a04ProjectCount = (int) ($statusProjectCountsByLabel['A04 (Canceled/Failed)'] ?? 0);
        $a05ProjectCount = (int) ($statusProjectCountsByLabel['A05 (Die Go/Berhasil)'] ?? 0);
        $totalProjectCount = (int) $costingData->count();
        $statusProjectTotal = $totalProjectCount;

        $statusProjectData = collect([
            [
                'label' => 'A00 (RFQ/RFI)',
                'count' => $a00ProjectCount,
                'color' => '#3b82f6',
            ],
            [
                'label' => 'A04 (Canceled/Failed)',
                'count' => $a04ProjectCount,
                'color' => '#f97316',
            ],
            [
                'label' => 'A05 (Die Go/Berhasil)',
                'count' => $a05ProjectCount,
                'color' => '#22c55e',
            ],
        ])->map(function ($item) use ($statusProjectTotal, $statusPotentialCostByLabel) {
            $percentage = $statusProjectTotal > 0
                ? (((int) $item['count'] / $statusProjectTotal) * 100)
                : 0;

            return [
                'label' => $item['label'],
                'count' => (int) ($item['count'] ?? 0),
                'percentage' => round($percentage, 1),
                'color' => $item['color'] ?? '#94a3b8',
                'potential_cost' => (float) ($statusPotentialCostByLabel[$item['label']] ?? 0),
            ];
        })->values();

        $pieSegments = [];
        $pieStartAngle = 0.0;
        foreach ($statusProjectData as $statusItem) {
            $count = (int) ($statusItem['count'] ?? 0);
            if ($count <= 0 || $statusProjectTotal <= 0) {
                continue;
            }

            $sliceAngle = ($count / $statusProjectTotal) * 360;
            $pieEndAngle = $pieStartAngle + $sliceAngle;
            $pieSegments[] = $statusItem['color']
                . ' '
                . number_format($pieStartAngle, 2, '.', '')
                . 'deg '
                . number_format($pieEndAngle, 2, '.', '')
                . 'deg';
            $pieStartAngle = $pieEndAngle;
        }

        if (empty($pieSegments)) {
            $statusProjectPieGradient = 'conic-gradient(#e2e8f0 0deg 360deg)';
        } else {
            if ($pieStartAngle < 360) {
                $pieSegments[] = '#e2e8f0 '
                    . number_format($pieStartAngle, 2, '.', '')
                    . 'deg 360deg';
            }
            $statusProjectPieGradient = 'conic-gradient(' . implode(', ', $pieSegments) . ')';
        }

        // Aggregate by assy to reflect project-level costing records.
        $costPerProduct = $costingData
            ->groupBy(function ($item) use ($resolveAssyLabel) {
                return Str::lower($resolveAssyLabel($item));
            })
            ->map(function ($items) use ($resolveUnitQty, $resolveAssyLabel, $resolvePotentialSales) {
                $first = $items->first();
                $productName = $resolveAssyLabel($first);

                $productTotalCost = $items->sum('total_cost');
                $productQty = $items->sum(function ($row) use ($resolveUnitQty) {
                    return $resolveUnitQty($row);
                });
                $materialCost = $items->sum('material_cost');
                $laborCost = $items->sum('labor_cost');
                $effectiveOverheadCost = $items->sum(function ($row) {
                    return (float) $row->overhead_cost + (float) $row->scrap_cost;
                });

                return [
                    'name' => $productName,
                    'total_cost' => $productTotalCost,
                    'total_qty' => $productQty,
                    'cost_per_unit' => $productQty > 0 ? ($productTotalCost / $productQty) : 0,
                    'potential_sales' => $items->sum(function ($row) use ($resolvePotentialSales) {
                        return $resolvePotentialSales($row);
                    }),
                    'material_cost' => $materialCost,
                    'labor_cost' => $laborCost,
                    'overhead_cost' => $effectiveOverheadCost,
                ];
            })
            ->sortByDesc(function ($item) {
                return ((float) $item['cost_per_unit'] * 1000000) + (float) $item['total_cost'];
            })
            ->values();

        // Find highest cost per unit product from aggregated dataset.
        $highestCostProduct = $costPerProduct->first();

        // Get max cost for chart scaling
        $maxCostPerUnit = $costPerProduct->max('cost_per_unit') ?: 1;

        // Get trend data (last 6 periods) from real costing records.
        $trendPeriodCandidates = CostingData::query()
            ->select('period')
            ->distinct()
            ->orderBy('period', 'desc')
            ->limit(6)
            ->pluck('period')
            ->reverse()
            ->values();

        $trendScope = CostingData::query();
        $applyFilters($trendScope);

        $trendData = $trendPeriodCandidates->map(function ($trendPeriod) use ($trendScope, $resolvePotentialSales) {
            $items = (clone $trendScope)->where('period', $trendPeriod)->get();
            $totalPotentialSalesPerPeriod = $items->sum(function ($row) use ($resolvePotentialSales) {
                return $resolvePotentialSales($row);
            });

            $label = $trendPeriod;
            if (preg_match('/^\d{4}-\d{2}$/', (string) $trendPeriod) === 1) {
                $label = \Carbon\Carbon::createFromFormat('Y-m', (string) $trendPeriod)->format('M y');
            }

            return [
                'period' => $trendPeriod,
                'period_label' => $label,
                'potential_sales' => $totalPotentialSalesPerPeriod,
            ];
        })->values();

        $maxTrendCost = $trendData->max('potential_sales') ?: 1;

        $monthlyProductCounts = $trendPeriodCandidates->map(function ($trendPeriod) use ($trendScope, $resolveAssyLabel) {
            $items = (clone $trendScope)
                ->with('product')
                ->where('period', $trendPeriod)
                ->get();

            $count = $items
                ->map(function ($row) use ($resolveAssyLabel) {
                    return Str::lower($resolveAssyLabel($row));
                })
                ->filter(function ($label) {
                    return trim((string) $label) !== '';
                })
                ->unique()
                ->count();

            $label = $trendPeriod;
            if (preg_match('/^\d{4}-\d{2}$/', (string) $trendPeriod) === 1) {
                $label = \Carbon\Carbon::createFromFormat('Y-m', (string) $trendPeriod)->format('M y');
            }

            return [
                'period' => $trendPeriod,
                'period_label' => $label,
                'count' => $count,
            ];
        })->values();

        $maxMonthlyProducts = $monthlyProductCounts->max('count') ?: 1;

        // Get top 5 customers by revenue
        $topCustomers = $costingData
            ->groupBy('customer_id')
            ->map(function ($items) {
                return [
                    'name' => $items->first()->customer->name ?? ('Customer #' . $items->first()->customer_id),
                    'revenue' => $items->sum('revenue'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        $maxRevenue = $topCustomers->max('revenue') ?: 1;

        $businessCategorySales = $costingData
            ->groupBy(function ($item) use ($resolveBusinessCategoryLabel) {
                return $resolveBusinessCategoryLabel($item);
            })
            ->map(function ($items, $label) use ($resolvePotentialSales) {
                $materialCost = (float) $items->sum('material_cost');
                $laborCost = (float) $items->sum('labor_cost');
                $overheadCost = $items->sum(function ($row) {
                    return (float) $row->overhead_cost + (float) $row->scrap_cost;
                });

                return [
                    'name' => $label,
                    'potential_sales' => $items->sum(function ($row) use ($resolvePotentialSales) {
                        return $resolvePotentialSales($row);
                    }),
                    'project_count' => $items->count(),
                    'material_cost' => $materialCost,
                    'labor_cost' => $laborCost,
                    'overhead_cost' => $overheadCost,
                ];
            })
            ->sortByDesc('potential_sales')
            ->values();

        $maxBusinessCategorySales = $businessCategorySales->max('potential_sales') ?: 1;

        $analysisMode = 'business_category';
        if ($modelFilter !== '' && $modelFilter !== 'all') {
            $analysisMode = 'assy_no';
        } elseif ($customerFilter !== '' && $customerFilter !== 'all') {
            $analysisMode = 'model';
        } elseif ($businessCategoryFilter !== '' && $businessCategoryFilter !== 'all') {
            $analysisMode = 'customer';
        }

        $analysisDimensionLabel = match ($analysisMode) {
            'assy_no' => 'Assy No',
            'model' => 'Model',
            'customer' => 'Customer',
            default => 'Business Category',
        };
        $showCustomerPerspective = $analysisMode === 'customer';

        $analysisSalesRows = $analysisMode === 'assy_no'
            ? $costingData
                ->groupBy(function ($item) {
                    $assyNo = trim((string) ($item->assy_no ?? ''));
                    return $assyNo !== '' ? $assyNo : '-';
                })
                ->map(function ($items, $assyNo) use ($resolvePotentialSales) {
                    $materialCost = (float) $items->sum('material_cost');
                    $laborCost = (float) $items->sum('labor_cost');
                    $overheadCost = $items->sum(function ($row) {
                        return (float) $row->overhead_cost + (float) $row->scrap_cost;
                    });

                    return [
                        'dimension_key' => (string) $assyNo,
                        'name' => (string) $assyNo,
                        'potential_sales' => $items->sum(function ($row) use ($resolvePotentialSales) {
                            return $resolvePotentialSales($row);
                        }),
                        'project_count' => $items->count(),
                        'material_cost' => $materialCost,
                        'labor_cost' => $laborCost,
                        'overhead_cost' => $overheadCost,
                    ];
                })
                ->sortByDesc('potential_sales')
                ->values()
            : ($analysisMode === 'model'
            ? $costingData
                ->groupBy(function ($item) {
                    $modelName = trim((string) ($item->model ?? ''));
                    return $modelName !== '' ? $modelName : '-';
                })
                ->map(function ($items, $modelName) use ($resolvePotentialSales) {
                    $materialCost = (float) $items->sum('material_cost');
                    $laborCost = (float) $items->sum('labor_cost');
                    $overheadCost = $items->sum(function ($row) {
                        return (float) $row->overhead_cost + (float) $row->scrap_cost;
                    });

                    return [
                        'dimension_key' => (string) $modelName,
                        'name' => (string) $modelName,
                        'potential_sales' => $items->sum(function ($row) use ($resolvePotentialSales) {
                            return $resolvePotentialSales($row);
                        }),
                        'project_count' => $items->count(),
                        'material_cost' => $materialCost,
                        'labor_cost' => $laborCost,
                        'overhead_cost' => $overheadCost,
                    ];
                })
                ->sortByDesc('potential_sales')
                ->values()
            : ($analysisMode === 'customer'
                ? $costingData
                    ->groupBy(function ($item) {
                        return (string) ($item->customer_id ?? '0');
                    })
                    ->map(function ($items, $customerId) use ($resolvePotentialSales) {
                        $materialCost = (float) $items->sum('material_cost');
                        $laborCost = (float) $items->sum('labor_cost');
                        $overheadCost = $items->sum(function ($row) {
                            return (float) $row->overhead_cost + (float) $row->scrap_cost;
                        });

                        return [
                            'dimension_key' => (string) $customerId,
                            'name' => $items->first()->customer->name ?? ('Customer #' . $customerId),
                            'potential_sales' => $items->sum(function ($row) use ($resolvePotentialSales) {
                                return $resolvePotentialSales($row);
                            }),
                            'project_count' => $items->count(),
                            'material_cost' => $materialCost,
                            'labor_cost' => $laborCost,
                            'overhead_cost' => $overheadCost,
                        ];
                    })
                    ->sortByDesc('potential_sales')
                    ->values()
                : $businessCategorySales
                    ->map(function ($item) {
                        return [
                            'dimension_key' => (string) ($item['name'] ?? ''),
                            'name' => (string) ($item['name'] ?? '-'),
                            'potential_sales' => (float) ($item['potential_sales'] ?? 0),
                            'project_count' => (int) ($item['project_count'] ?? 0),
                            'material_cost' => (float) ($item['material_cost'] ?? 0),
                            'labor_cost' => (float) ($item['labor_cost'] ?? 0),
                            'overhead_cost' => (float) ($item['overhead_cost'] ?? 0),
                        ];
                    })
                    ->values()));

        $topCustomerPotentialSales = $costingData
            ->groupBy('customer_id')
            ->map(function ($items) {
                $customerName = $items->first()->customer->name ?? ('Customer #' . $items->first()->customer_id);
                $resolvePotentialSales = function ($row) {
                    $qtyPerMonth = (float) ($row->forecast ?? 0);
                    $productLifeYears = (float) ($row->project_period ?? 0);
                    $cogm = (float) ($row->material_cost ?? 0)
                        + (float) ($row->labor_cost ?? 0)
                        + (float) ($row->overhead_cost ?? 0)
                        + (float) ($row->scrap_cost ?? 0);

                    return $qtyPerMonth * $productLifeYears * $cogm;
                };

                $categoryBreakdown = $items
                    ->groupBy(function ($item) {
                        $line = trim((string) ($item->product->line ?? ''));
                        if ($line !== '') {
                            return $line;
                        }

                        $productName = trim((string) ($item->product->name ?? ''));
                        return $productName !== '' ? $productName : 'Uncategorized';
                    })
                    ->map(function ($categoryItems, $categoryName) {
                        $categoryPotentialSales = $categoryItems->sum(function ($row) {
                            $qtyPerMonth = (float) ($row->forecast ?? 0);
                            $productLifeYears = (float) ($row->project_period ?? 0);
                            $cogm = (float) ($row->material_cost ?? 0)
                                + (float) ($row->labor_cost ?? 0)
                                + (float) ($row->overhead_cost ?? 0)
                                + (float) ($row->scrap_cost ?? 0);

                            return $qtyPerMonth * $productLifeYears * $cogm;
                        });

                        return [
                            'category' => $categoryName,
                            'potential_sales' => $categoryPotentialSales,
                        ];
                    })
                    ->sortByDesc('potential_sales')
                    ->values();

                $dominantCategory = $categoryBreakdown->first();

                return [
                    'customer_name' => $customerName,
                    'business_category' => $dominantCategory['category'] ?? '-',
                    'potential_sales' => $items->sum(function ($row) use ($resolvePotentialSales) {
                        return $resolvePotentialSales($row);
                    }),
                ];
            })
            ->sortByDesc('potential_sales')
            ->take(5)
            ->values();

        // Material breakdown summary
        $materialBreakdown = $costPerProduct->map(function ($item) {
            $effectiveOverheadCost = (float) ($item['overhead_cost'] ?? 0);
            $materialCost = (float) ($item['material_cost'] ?? 0);
            $laborCost = (float) ($item['labor_cost'] ?? 0);
            $total = $materialCost + $laborCost + $effectiveOverheadCost;

            return [
                'name' => $item['name'] ?? '-',
                'material_pct' => $total > 0 ? ($materialCost / $total) * 100 : 0,
                'labor_pct' => $total > 0 ? ($laborCost / $total) * 100 : 0,
                'overhead_pct' => $total > 0 ? ($effectiveOverheadCost / $total) * 100 : 0,
            ];
        });

        // Count projects per business category broken down by status (A00, A04, A05)
        $projectCountPerCustomer = collect();
        foreach ($analysisSalesRows as $dimensionRow) {
            $dimensionKey = (string) ($dimensionRow['dimension_key'] ?? '');
            if ($dimensionKey === '') {
                continue;
            }

            $categoryItems = $costingData->filter(function ($item) use ($analysisMode, $dimensionKey, $resolveBusinessCategoryLabel) {
                if ($analysisMode === 'assy_no') {
                    $assyNo = trim((string) ($item->assy_no ?? ''));
                    return ($assyNo !== '' ? $assyNo : '-') === $dimensionKey;
                }

                if ($analysisMode === 'model') {
                    $modelName = trim((string) ($item->model ?? ''));
                    return ($modelName !== '' ? $modelName : '-') === $dimensionKey;
                }

                if ($analysisMode === 'customer') {
                    return (string) ($item->customer_id ?? '') === $dimensionKey;
                }

                return $resolveBusinessCategoryLabel($item) === $dimensionKey;
            })->values();

            $a00Count = 0;
            $a04Count = 0;
            $a05Count = 0;
            foreach ($categoryItems as $item) {
                $revision = $item->trackingRevision;
                if (!$revision) {
                    continue;
                }

                if (($revision->a05 ?? null) === 'ada') {
                    $a05Count++;
                } elseif (($revision->a04 ?? null) === 'ada') {
                    $a04Count++;
                } elseif (($revision->a00 ?? null) === 'ada') {
                    $a00Count++;
                }
            }

            $totalCount = $categoryItems->count();
            if ($totalCount > 0) {
                $projectCountPerCustomer->push([
                    'name' => (string) ($dimensionRow['name'] ?? '-'),
                    'a00_count' => $a00Count,
                    'a04_count' => $a04Count,
                    'a05_count' => $a05Count,
                    'total_count' => $totalCount,
                ]);
            }
        }
        $projectCountPerCustomer = $projectCountPerCustomer
            ->take(8)
            ->values();
        $maxProjectCount = $projectCountPerCustomer->max('total_count') ?: 1;

        return view('dashboard', compact(
            'period',
            'businessCategoryFilter',
            'customerFilter',
            'modelFilter',
            'businessCategories',
            'customers',
            'models',
            'costingData',
            'totalCost',
            'totalQty',
            'estimatedQtyProduksi',
            'avgCostPerUnit',
            'highestCostProduct',
            'costPerProduct',
            'maxCostPerUnit',
            'projectCountPerCustomer',
            'maxProjectCount',
            'trendData',
            'maxTrendCost',
            'monthlyProductCounts',
            'maxMonthlyProducts',
            'topCustomers',
            'maxRevenue',
            'businessCategorySales',
            'analysisSalesRows',
            'analysisDimensionLabel',
            'showCustomerPerspective',
            'maxBusinessCategorySales',
            'topCustomerPotentialSales',
            'materialBreakdown',
            'periods',
            'periodDisplayLabel',
            'totalProjectCount',
            'a00ProjectCount',
            'a04ProjectCount',
            'a05ProjectCount',
            'statusProjectData',
            'statusProjectTotal',
            'statusProjectPieGradient',
            'totalSubmitCostingMonthly',
            'monthlySubmitCounts',
            'maxMonthlySubmitCount'
        ));
    }

    public function form(Request $request)
    {
        $products = Product::all();
        $businessCategories = BusinessCategory::orderBy('code')->orderBy('name')->get();
        $customers = Customer::all();
        $materials = $this->validMasterMaterialsQuery()
            ->orderBy('material_code')
            ->get();
        $cycleTimeTemplates = CycleTimeTemplate::orderBy('id')->get();
        $plants = Plant::orderBy('code')->orderBy('name')->get();
        $periods = CostingData::distinct('period')->orderBy('period', 'desc')->pluck('period');
        $wireRates = WireRate::orderBy('period_month', 'asc')->orderBy('id', 'asc')->get();
        $selectedWireRateId = (int) session('wire_selected_rate_id', 0);

        if ($selectedWireRateId <= 0 && $wireRates->isNotEmpty()) {
            $selectedWireRateId = (int) $wireRates->last()->id;
        }

        $activeWireRate = $wireRates->firstWhere('id', $selectedWireRateId);
        if (!$activeWireRate) {
            $activeWireRate = $wireRates->last();
            $selectedWireRateId = (int) ($activeWireRate?->id ?? 0);
        }

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
                    ->whereNotNull('part_name')
                    ->where('part_name', '!=', '')
                    ->orderBy('part_number')
                    ->get()
                    ->map(function ($item) {
                        $partNumber = trim((string) ($item->part_number ?? ''));
                        $partName = trim((string) ($item->part_name ?? ''));

                        $matchedMaterials = collect();
                        if ($partNumber !== '' && $partNumber !== '-') {
                            $matchedMaterials = Material::query()
                                ->where(function ($query) {
                                    $query->whereNull('material_code')
                                        ->orWhere('material_code', 'not like', '__ROW_%');
                                })
                                ->whereNotNull('material_description')
                                ->where('material_description', '!=', '')
                                ->whereRaw('LOWER(material_description) = ?', [Str::lower($partNumber)])
                                ->orderBy('material_code')
                                ->get();
                        }

                        if ($matchedMaterials->isEmpty() && $partName !== '' && $partName !== '-') {
                            $matchedMaterials = Material::query()
                                ->where(function ($query) {
                                    $query->whereNull('material_code')
                                        ->orWhere('material_code', 'not like', '__ROW_%');
                                })
                                ->whereNotNull('material_description')
                                ->where('material_description', '!=', '')
                                ->whereRaw('LOWER(material_description) = ?', [Str::lower($partName)])
                                ->orderBy('material_code')
                                ->get();
                        }

                        $item->matched_materials = $matchedMaterials;

                        $firstMatched = $matchedMaterials->first();
                        if ($firstMatched) {
                            $item->matched_material_description = $firstMatched->material_description;
                            $item->matched_price = $firstMatched->price;
                            $item->matched_purchase_unit = $firstMatched->purchase_unit;
                            $item->matched_currency = $firstMatched->currency;
                            $item->matched_moq = $firstMatched->moq;
                            $item->matched_cn = $firstMatched->cn;
                            $item->matched_maker = $firstMatched->maker;
                            $item->matched_add_cost_import_tax = $firstMatched->add_cost_import_tax;
                            $item->matched_price_update = $firstMatched->price_update;
                            $item->matched_price_before = $firstMatched->price_before;
                        }

                        // Lookup wire prices
                        $item->matched_wires = collect();
                        if ($partNumber !== '' && $partNumber !== '-') {
                            $item->matched_wires = Wire::query()
                                ->whereRaw('LOWER(item) = ?', [Str::lower($partNumber)])
                                ->orWhereRaw('LOWER(idcode) = ?', [Str::lower($partNumber)])
                                ->get();
                        }

                        if ($item->matched_wires->isEmpty() && $partName !== '' && $partName !== '-') {
                            $item->matched_wires = Wire::query()
                                ->whereRaw('LOWER(item) = ?', [Str::lower($partName)])
                                ->orWhereRaw('LOWER(idcode) = ?', [Str::lower($partName)])
                                ->get();
                        }

                        return $item;
                    });

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
                        $productLineNorm = $normalize($matchedProduct->line ?? '');
                        $productCodeNorm = $normalize($matchedProduct->code ?? '');
                        $productNameNorm = $normalize($matchedProduct->name ?? '');

                        $matchedBusinessCategory = $businessCategories->first(function ($category) use ($normalize, $productLineNorm, $productCodeNorm, $productNameNorm) {
                            $categoryCodeNorm = $normalize($category->code ?? '');
                            $categoryNameNorm = $normalize($category->name ?? '');

                            return ($productLineNorm !== '' && ($categoryNameNorm === $productLineNorm || str_contains($categoryNameNorm, $productLineNorm) || str_contains($productLineNorm, $categoryNameNorm)))
                                || ($productCodeNorm !== '' && $categoryCodeNorm === $productCodeNorm)
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

        if (!$costingDataId && $trackingRevisionId) {
            $costingDataId = CostingData::where('tracking_revision_id', $trackingRevisionId)
                ->latest('id')
                ->value('id');
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
            'wireRates',
            'activeWireRate',
            'selectedWireRateId',
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
        $updateSection = trim((string) $request->input('update_section', ''));
        $importPartlistFileUploaded = $request->hasFile('import_partlist_file');
        $importCycleTimeFileUploaded = $request->hasFile('import_cycle_time_file');

        if ($importPartlistFileUploaded && $updateSection === '') {
            $updateSection = 'material';
        } elseif ($importCycleTimeFileUploaded && $updateSection === '') {
            $updateSection = 'cycle_time';
        }

        $baseRules = [
            'costing_data_id' => 'nullable|exists:costing_data,id',
            'tracking_revision_id' => 'nullable|exists:document_revisions,id',
            'update_section' => 'nullable|string',
            'import_partlist' => 'nullable|boolean',
            'import_cycle_time' => 'nullable|boolean',
            'wire_rate_id' => 'nullable|exists:wire_rates,id',
        ];

        $fullRules = [
            'business_category_id' => 'required|exists:business_categories,id',
            'customer_id' => 'required|exists:customers,id',
            'period' => 'required|string',
            'line' => 'nullable|string',
            'model' => 'nullable|string',
            'assy_no' => 'nullable|string',
            'assy_name' => 'nullable|string',
            'exchange_rate_usd' => 'required|numeric',
            'exchange_rate_jpy' => 'required|numeric',
            'lme_rate' => 'nullable|numeric',
            'wire_rate_id' => 'nullable|exists:wire_rates,id',
            'forecast' => 'required|integer',
            'project_period' => 'required|integer',
            'material_cost' => 'nullable|numeric',
            'labor_cost' => 'nullable|numeric',
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
            'materials.*.unit_price_basis' => 'nullable|string',
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
            'cycle_times.*.area_of_process' => 'nullable|in:PP - Preparation,FA - Final Assy',
        ];

        $sectionRules = [
            'informasi_project' => [
                'business_category_id' => 'required|exists:business_categories,id',
                'customer_id' => 'required|exists:customers,id',
                'period' => 'required|string',
                'line' => 'nullable|string',
                'model' => 'nullable|string',
                'assy_no' => 'nullable|string',
                'assy_name' => 'nullable|string',
                'forecast' => 'required|integer',
                'project_period' => 'required|integer',
            ],
            'rates' => [
                'wire_rate_id' => 'required|exists:wire_rates,id',
                'exchange_rate_usd' => 'required|numeric',
                'exchange_rate_jpy' => 'required|numeric',
                'lme_rate' => 'nullable|numeric',
            ],
            'material' => [
                'forecast' => 'required|integer',
                'project_period' => 'required|integer',
                'materials' => 'nullable|array',
                'materials.*.part_no' => 'nullable|string',
                'materials.*.part_name' => 'nullable|string',
                'materials.*.qty_req' => 'nullable|numeric',
                'materials.*.unit' => 'nullable|string',
                'materials.*.amount1' => 'nullable|numeric',
                'materials.*.unit_price_basis' => 'nullable|string',
                'materials.*.qty_moq' => 'nullable|numeric',
                'materials.*.cn_type' => 'nullable|string',
                'materials.*.import_tax' => 'nullable|numeric',
                'manual_unpriced_prices' => 'nullable|array',
            ],
            'unpriced_parts' => [
                'manual_unpriced_prices' => 'nullable|array',
            ],
            'cycle_time' => [
                'cycle_times' => 'nullable|array',
                'cycle_times.*.process' => 'nullable|string',
                'cycle_times.*.qty' => 'nullable|numeric',
                'cycle_times.*.time_hour' => 'nullable|numeric',
                'cycle_times.*.time_sec' => 'nullable|numeric',
                'cycle_times.*.time_sec_per_qty' => 'nullable|numeric',
                'cycle_times.*.cost_per_sec' => 'nullable|numeric',
                'cycle_times.*.cost_per_unit' => 'nullable|numeric',
                'cycle_times.*.area_of_process' => 'nullable|in:PP - Preparation,FA - Final Assy',
            ],
            'resume_cogm' => [
                'material_cost' => 'nullable|numeric',
                'labor_cost' => 'nullable|numeric',
                'overhead_cost' => 'nullable|numeric',
                'scrap_cost' => 'nullable|numeric',
                'revenue' => 'nullable|numeric',
                'qty_good' => 'nullable|integer',
            ],
        ];

        if ($updateSection !== '' && !array_key_exists($updateSection, $sectionRules)) {
            return back()->with('error', 'Section update tidak valid.')->withInput();
        }

        $rules = $updateSection !== ''
            ? array_merge($baseRules, $sectionRules[$updateSection])
            : array_merge($baseRules, $fullRules);

        if ($updateSection === 'material' && ($request->boolean('import_partlist') || $importPartlistFileUploaded)) {
            $rules['import_partlist_file'] = 'nullable';
        }

        if ($updateSection === 'cycle_time' && ($request->boolean('import_cycle_time') || $importCycleTimeFileUploaded)) {
            $rules['import_cycle_time_file'] = 'nullable';
        }

        $messages = [
            'import_partlist_file.required' => 'File partlist wajib dipilih.',
            'import_partlist_file.file' => 'File partlist tidak valid.',
            'import_partlist_file.uploaded' => 'Upload gagal. Kemungkinan ukuran file melebihi batas server. Naikkan upload_max_filesize dan post_max_size di PHP.',
            'import_partlist_file.mimes' => 'Format file harus .xlsx atau .xls sesuai template partlist.',
            'import_partlist_file.max' => 'Ukuran file partlist terlalu besar (maks 20MB).',
            'import_cycle_time_file.required' => 'File Cycle Time wajib dipilih.',
            'import_cycle_time_file.file' => 'File Cycle Time tidak valid.',
            'import_cycle_time_file.uploaded' => 'Upload file Cycle Time gagal. Kemungkinan ukuran file melebihi batas server. Naikkan upload_max_filesize dan post_max_size di PHP.',
            'import_cycle_time_file.mimes' => 'Format file Cycle Time harus .xlsx atau .xls.',
            'import_cycle_time_file.max' => 'Ukuran file Cycle Time terlalu besar (maks 20MB).',
        ];

        $validated = $request->validate($rules, $messages);

        if ($updateSection === 'rates' && !empty($validated['wire_rate_id'])) {
            $selectedWireRate = WireRate::find((int) $validated['wire_rate_id']);
            if ($selectedWireRate) {
                session(['wire_selected_rate_id' => (int) $selectedWireRate->id]);

                // Force rates to follow selected wire rate from database.
                $request->merge([
                    'exchange_rate_usd' => (float) ($selectedWireRate->usd_rate ?? 0),
                    'exchange_rate_jpy' => (float) ($selectedWireRate->jpy_rate ?? 0),
                    'lme_rate' => (float) ($selectedWireRate->lme_active ?? 0),
                ]);
            }
        }

        $importRequested = $updateSection === 'material' && ($request->boolean('import_partlist') || $importPartlistFileUploaded);
        $importFromPartlist = $updateSection === 'material' && $importPartlistFileUploaded;
        $importedMaterialRows = [];
        $importCycleTimeRequested = $updateSection === 'cycle_time' && ($request->boolean('import_cycle_time') || $importCycleTimeFileUploaded);
        $importFromCycleTime = $updateSection === 'cycle_time' && $importCycleTimeFileUploaded;
        $importedCycleTimeRows = [];

        if ($importRequested) {
            $trackingRevisionId = isset($validated['tracking_revision_id']) ? (int) $validated['tracking_revision_id'] : null;
            $uploadedPartlistFile = $request->file('import_partlist_file');
            $uploadErrorCode = (int) ($_FILES['import_partlist_file']['error'] ?? UPLOAD_ERR_NO_FILE);

            if ($uploadedPartlistFile) {
                if (!$uploadedPartlistFile->isValid()) {
                    $errorCode = (int) $uploadedPartlistFile->getError();
                    return back()->with('error', 'Upload file partlist gagal: ' . $this->uploadErrorCodeToMessage($errorCode))->withInput();
                }
            }

            if ($uploadedPartlistFile) {
                $ext = strtolower((string) $uploadedPartlistFile->getClientOriginalExtension());
                if (!in_array($ext, ['xlsx', 'xls'], true)) {
                    return back()->with('error', 'Format file harus .xlsx atau .xls sesuai template partlist.')->withInput();
                }

                if ($uploadedPartlistFile->getSize() > (20 * 1024 * 1024)) {
                    return back()->with('error', 'Ukuran file partlist terlalu besar (maks 20MB).')->withInput();
                }
            } else {
                if ($uploadErrorCode !== UPLOAD_ERR_NO_FILE) {
                    return back()->with('error', 'Upload file partlist gagal: ' . $this->uploadErrorCodeToMessage($uploadErrorCode))->withInput();
                }

                if ($uploadErrorCode === UPLOAD_ERR_NO_FILE && !$trackingRevisionId) {
                    return back()->with('warning', 'Silakan pilih file partlist terlebih dahulu sebelum import.')->withInput();
                }
            }

            $importResult = $this->loadPartlistMaterialRows($trackingRevisionId, $uploadedPartlistFile);

            if (!empty($importResult['error'])) {
                return back()->with('error', $importResult['error'])->withInput();
            }

            $importedMaterialRows = array_values($importResult['rows']);

            if (count($importedMaterialRows) === 0) {
                return back()->with('warning', 'Data partlist tidak ditemukan. Pastikan data diisi mulai kolom D-J dari baris 12 ke bawah (sesuai template).')->withInput();
            }
        }

        if ($importCycleTimeRequested) {
            $uploadedCycleTimeFile = $request->file('import_cycle_time_file');
            $uploadErrorCode = (int) ($_FILES['import_cycle_time_file']['error'] ?? UPLOAD_ERR_NO_FILE);

            if ($uploadedCycleTimeFile) {
                if (!$uploadedCycleTimeFile->isValid()) {
                    $errorCode = (int) $uploadedCycleTimeFile->getError();
                    return back()->with('error', 'Upload file Cycle Time gagal: ' . $this->uploadErrorCodeToMessage($errorCode))->withInput();
                }

                $ext = strtolower((string) $uploadedCycleTimeFile->getClientOriginalExtension());
                if (!in_array($ext, ['xlsx', 'xls'], true)) {
                    return back()->with('error', 'Format file Cycle Time harus .xlsx atau .xls.')->withInput();
                }

                if ($uploadedCycleTimeFile->getSize() > (20 * 1024 * 1024)) {
                    return back()->with('error', 'Ukuran file Cycle Time terlalu besar (maks 20MB).')->withInput();
                }
            } elseif ($uploadErrorCode !== UPLOAD_ERR_NO_FILE) {
                return back()->with('error', 'Upload file Cycle Time gagal: ' . $this->uploadErrorCodeToMessage($uploadErrorCode))->withInput();
            } else {
                return back()->with('warning', 'Silakan pilih file Cycle Time terlebih dahulu sebelum import.')->withInput();
            }

            $importResult = $this->loadCycleTimeRows($uploadedCycleTimeFile);

            if (!empty($importResult['error'])) {
                return back()->with('error', $importResult['error'])->withInput();
            }

            $importedCycleTimeRows = array_values($importResult['rows']);

            if (count($importedCycleTimeRows) === 0) {
                return back()->with('warning', 'Data Cycle Time tidak ditemukan. Pastikan data No/Process/Qty/Time/Area diisi sesuai template (B18, C18, D17, G18, I18 ke bawah).')->withInput();
            }

            $request->merge(['cycle_times' => $importedCycleTimeRows]);
        }

        DB::beginTransaction();
        try {
            $costingDataId = $validated['costing_data_id'] ?? null;
            $trackingRevisionId = $validated['tracking_revision_id'] ?? null;
            $costingData = null;

            if ($costingDataId) {
                $costingData = CostingData::whereKey($costingDataId)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$costingData && $trackingRevisionId) {
                $costingData = CostingData::where('tracking_revision_id', $trackingRevisionId)
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();
            }

            $productId = $costingData?->product_id;
            if ($request->filled('business_category_id')) {
                $businessCategory = BusinessCategory::findOrFail((int) $request->input('business_category_id'));
                $product = Product::firstOrCreate(
                    ['code' => trim((string) $businessCategory->code)],
                    ['name' => trim((string) $businessCategory->name)]
                );

                if (trim((string) $product->name) !== trim((string) $businessCategory->name)) {
                    $product->update(['name' => trim((string) $businessCategory->name)]);
                }

                $productId = $product->id;
            }

            $fillableRequestFields = [
                'customer_id',
                'tracking_revision_id',
                'period',
                'line',
                'model',
                'assy_no',
                'assy_name',
                'exchange_rate_usd',
                'exchange_rate_jpy',
                'lme_rate',
                'forecast',
                'project_period',
                'material_cost',
                'labor_cost',
                'overhead_cost',
                'scrap_cost',
                'revenue',
                'qty_good',
                'cycle_times',
            ];

            $basePayload = $request->only($fillableRequestFields);

            // Parse numeric fields properly, removing formatting
            if (array_key_exists('material_cost', $basePayload)) {
                $basePayload['material_cost'] = $this->parseNumericInput($validated['material_cost'] ?? 0);
            }

            if (array_key_exists('labor_cost', $basePayload)) {
                $basePayload['labor_cost'] = $this->parseNumericInput($validated['labor_cost'] ?? 0);
            }

            if (array_key_exists('overhead_cost', $basePayload)) {
                $basePayload['overhead_cost'] = $this->parseNumericInput($validated['overhead_cost'] ?? 0);
            }

            if (array_key_exists('scrap_cost', $basePayload)) {
                $basePayload['scrap_cost'] = $this->parseNumericInput($validated['scrap_cost'] ?? 0);
            }

            $sectionPayloadMap = [
                'informasi_project' => ['customer_id', 'tracking_revision_id', 'period', 'line', 'model', 'assy_no', 'assy_name', 'forecast', 'project_period'],
                'rates' => ['exchange_rate_usd', 'exchange_rate_jpy', 'lme_rate', 'tracking_revision_id'],
                'material' => ['forecast', 'project_period', 'material_cost', 'labor_cost', 'overhead_cost', 'scrap_cost', 'revenue', 'qty_good', 'tracking_revision_id'],
                'unpriced_parts' => ['tracking_revision_id'],
                'cycle_time' => ['cycle_times', 'tracking_revision_id'],
                'resume_cogm' => ['material_cost', 'labor_cost', 'overhead_cost', 'scrap_cost', 'revenue', 'qty_good', 'tracking_revision_id'],
            ];

            $payload = $updateSection !== ''
                ? array_intersect_key($basePayload, array_flip($sectionPayloadMap[$updateSection]))
                : $basePayload;

            if ($trackingRevisionId) {
                $payload['tracking_revision_id'] = $trackingRevisionId;
            }

            if ($productId) {
                $payload['product_id'] = $productId;
            }

            if ($costingData) {
                if (!empty($payload)) {
                    $costingData->update($payload);
                }
            } else {
                $requiredOnCreate = ['product_id', 'customer_id', 'period'];
                foreach ($requiredOnCreate as $requiredField) {
                    if (!array_key_exists($requiredField, $payload) || $payload[$requiredField] === null || $payload[$requiredField] === '') {
                        DB::rollBack();
                        return back()->with('error', 'Simpan Informasi Project terlebih dahulu sebelum update section lain.')->withInput();
                    }
                }

                $payload = array_merge([
                    'exchange_rate_usd' => 15500,
                    'exchange_rate_jpy' => 103,
                    'forecast' => 0,
                    'project_period' => 0,
                    'material_cost' => 0,
                    'labor_cost' => 0,
                    'overhead_cost' => 0,
                    'scrap_cost' => 0,
                    'revenue' => 0,
                    'qty_good' => 0,
                    'cycle_times' => [],
                ], $payload);

                $costingData = CostingData::create($payload);
            }

            $manualUnpricedPrices = collect($request->input('manual_unpriced_prices', []))
                ->mapWithKeys(function ($value, $key) {
                    return [strtolower(trim((string) $key)) => floatval($value)];
                });
            $partAggregation = [];

            $hasMaterialPayload = $request->has('materials') || !empty($importedMaterialRows);
            $shouldProcessMaterials = $updateSection === '' || $updateSection === 'material';
            $shouldProcessUnpricedOnly = $updateSection === 'unpriced_parts';
            // Keep existing material rows when material payload is missing, to avoid accidental data loss.
            $shouldSyncMaterialBreakdowns = $shouldProcessMaterials
                && $hasMaterialPayload;

            if ($shouldSyncMaterialBreakdowns) {
                MaterialBreakdown::where('costing_data_id', $costingData->id)->delete();
            }

            $materialsInput = $importFromPartlist
                ? $importedMaterialRows
                : $request->input('materials', []);

            if ($shouldSyncMaterialBreakdowns && is_array($materialsInput)) {
                foreach ($materialsInput as $rowIndex => $matData) {
                    $rowNo = trim((string) ($matData['row_no'] ?? ''));
                    $rowPartNo = trim((string) ($matData['part_no'] ?? ''));
                    $rowIdCode = trim((string) ($matData['id_code'] ?? ''));
                    $normalizedRowPartNo = ($rowPartNo === '-' ? '' : $rowPartNo);
                    $normalizedRowIdCode = ($rowIdCode === '-' ? '' : $rowIdCode);
                    $partNumber = $normalizedRowPartNo;

                    $masterMaterial = $this->findMasterMaterial($rowPartNo, $rowIdCode);
                    $materialCode = '__ROW_' . $costingData->id . '_' . $rowIndex;

                    $partKey = $partNumber !== ''
                        ? strtolower($partNumber)
                        : ('__row_' . strtolower($rowNo !== '' ? $rowNo : (string) $rowIndex));
                    $partNameInput = trim((string) ($matData['part_name'] ?? ''));
                    $qtyReqRaw = intval(round(floatval($matData['qty_req'] ?? 0)));
                    // Keep qty_req in a practical range to avoid exploding totals from malformed input.
                    $qtyReq = max(1, min(1000, $qtyReqRaw));
                    $unitPriceBasisRaw = trim((string) ($matData['unit_price_basis_text'] ?? $matData['unit_price_basis'] ?? ''));
                    $unitPriceBasisNumeric = $this->toFloatValue($unitPriceBasisRaw);
                    $manualPrice = floatval($manualUnpricedPrices->get($partKey, 0));

                    $resolvedUnit = $this->normalizeUnitValue($matData['unit'] ?? ($masterMaterial?->base_uom ?? 'PCS'));

                    $resolvedCurrency = strtoupper(trim((string) ($matData['currency'] ?? '')));
                    if ($resolvedCurrency === '' && $masterMaterial?->currency) {
                        $resolvedCurrency = strtoupper(trim((string) $masterMaterial->currency));
                    }
                    if (!in_array($resolvedCurrency, ['IDR', 'USD', 'JPY'], true)) {
                        $resolvedCurrency = 'IDR';
                    }

                    $resolvedSupplier = trim((string) ($matData['supplier'] ?? ''));
                    if ($resolvedSupplier === '' && $masterMaterial?->maker) {
                        $resolvedSupplier = trim((string) $masterMaterial->maker);
                    }

                    $qtyMoqRaw = trim((string) ($matData['qty_moq'] ?? ''));
                    $moq = floatval($matData['qty_moq'] ?? 0);
                    if ($qtyMoqRaw === '' && $masterMaterial?->moq !== null) {
                        $moq = floatval($masterMaterial->moq);
                    }
                    // Bound MOQ to a realistic range relative to qty requirement.
                    $maxMoq = max(1000, $qtyReq * 20);
                    $moq = max((float) $qtyReq, min($maxMoq, $moq));

                    $cnType = strtoupper(trim((string) ($matData['cn_type'] ?? '')));
                    if (!in_array($cnType, ['C', 'N', 'E'], true)) {
                        $cnType = strtoupper(trim((string) ($masterMaterial?->cn ?? 'N')));
                        if (!in_array($cnType, ['C', 'N', 'E'], true)) {
                            $cnType = 'N';
                        }
                    }

                    $importTaxRaw = trim((string) ($matData['import_tax'] ?? ''));
                    $importTax = floatval($matData['import_tax'] ?? 0);
                    if ($importTaxRaw === '' && $masterMaterial?->add_cost_import_tax !== null) {
                        $importTax = floatval($masterMaterial->add_cost_import_tax);
                    }

                    $priceBaseInput = $this->toFloatValue($matData['amount1'] ?? 0);
                    $masterPrice = floatval($masterMaterial?->price ?? 0);
                    $priceBase = $priceBaseInput;

                    $material = $masterMaterial;
                    if (!$material) {
                        $material = Material::firstOrCreate(
                            ['material_code' => $materialCode],
                            [
                                'material_description' => $partNameInput !== '' ? $partNameInput : null,
                                'base_uom' => $resolvedUnit,
                                'maker' => $resolvedSupplier !== '' ? $resolvedSupplier : null,
                                'currency' => $resolvedCurrency,
                                'price' => 0,
                            ]
                        );
                    }

                    // Keep Unit editable per current save flow: if user changes unit in material section,
                    // persist it so rendered rows stay consistent after update/refresh.
                    if ($material && $resolvedUnit !== '' && $resolvedUnit !== '-') {
                        $currentBaseUom = strtoupper(trim((string) ($material->base_uom ?? '')));
                        if ($currentBaseUom !== $resolvedUnit) {
                            $material->base_uom = $resolvedUnit;
                            $material->save();
                        }
                    }

                    $resolvedPartNameForRecap = $partNameInput;

                    // Re-calculate logic (replicating JS logic for safety)
                    $unit = strtoupper($resolvedUnit);
                    $forecast = $request->forecast;
                    $periodYear = $request->project_period;

                    // Multiply Factor Logic
                    $unitDivisor = ($unit === 'MM') ? 1000 : 1;
                    $denominator = $forecast * $periodYear * 12 * $qtyReq;
                    $denominator = ($denominator != 0) ? ($denominator / $unitDivisor) : 0;

                    $ratio = ($denominator != 0) ? ($moq / $denominator) : 0;

                    $multiplyFactor = ($cnType === 'C' || $ratio < 1) ? 1 : $ratio;

                    // Amount 2 Logic
                    $extra = $priceBase * ($importTax / 100);
                    $base = $priceBase + $extra;
                    $numerator = $multiplyFactor * $base;

                    $unitDivisor2 = in_array(strtoupper($unit), ['METER', 'M', 'MTR', 'MM']) ? 1000 : 1;
                    $amount2 = ($unitDivisor2 != 0) ? ($numerator / $unitDivisor2) : 0;

                    MaterialBreakdown::create([
                        'costing_data_id' => $costingData->id,
                        'material_id' => $material->id,
                        'row_no' => $rowNo !== '' ? $rowNo : null,
                        'part_no' => $normalizedRowPartNo !== '' ? $normalizedRowPartNo : null,
                        'id_code' => $normalizedRowIdCode !== '' ? $normalizedRowIdCode : null,
                        'part_name' => $partNameInput !== '' ? $partNameInput : null,
                        'pro_code' => trim((string) ($matData['pro_code'] ?? '')),
                        'qty_req' => $qtyReq,
                        'amount1' => $priceBase,
                        'unit_price_basis' => $unitPriceBasisNumeric,
                        'unit_price_basis_text' => $unitPriceBasisRaw !== '' ? $unitPriceBasisRaw : null,
                        'currency' => $resolvedCurrency,
                        'qty_moq' => $moq,
                        'cn_type' => $cnType,
                        'import_tax_percent' => $importTax,
                        'amount2' => $amount2,
                        'currency2' => $resolvedCurrency,
                        'unit_price2' => $amount2, // Saving calculated amount2 as unit_price2 default
                    ]);

                    $rowAmount1 = $priceBase;
                    $rowBasisPrice = $unitPriceBasisNumeric;
                    $detectedPrice = $importFromPartlist ? 0 : $masterPrice;
                    // Rule: jika Amount 1 sudah terisi, part tidak dianggap unpriced.
                    $isUnpriced = ($rowAmount1 <= 0)
                        && ($rowBasisPrice <= 0)
                        && ($manualPrice <= 0);

                    // Unpriced recap source: only valid Part No from material section.
                    if ($partNumber === '' || $partNumber === '-') {
                        continue;
                    }

                    if (!isset($partAggregation[$partKey])) {
                        $partAggregation[$partKey] = [
                            'part_number' => $partNumber,
                            'part_name' => $resolvedPartNameForRecap,
                            'detected_price' => $detectedPrice,
                            'manual_price' => $manualPrice > 0 ? $manualPrice : null,
                            'is_unpriced' => false,
                        ];
                    }

                    $partAggregation[$partKey]['is_unpriced'] = $partAggregation[$partKey]['is_unpriced'] || $isUnpriced;

                    if ($manualPrice > 0) {
                        $partAggregation[$partKey]['manual_price'] = $manualPrice;
                    }
                }
            }

            if ($trackingRevisionId && $shouldProcessMaterials && !$shouldProcessUnpricedOnly) {
                UnpricedPart::where('document_revision_id', $trackingRevisionId)
                    ->whereNull('resolved_at')
                    ->update([
                        'costing_data_id' => $costingData->id,
                        'resolved_at' => now(),
                        'resolution_source' => 'material_update_pending_rebuild',
                    ]);
            }

            if ($trackingRevisionId && $shouldProcessUnpricedOnly) {
                if ($shouldProcessMaterials) {
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
                                    'detected_price' => $partInfo['detected_price'],
                                    'manual_price' => null,
                                    'notes' => 'Auto-detected from Form Costing validation.',
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
                } else {
                    if ($costingData) {
                        // For recap-only update, do not apply manual input as resolved pricing yet.
                        // Manual apply must happen explicitly via the "Tambah" action in recap.
                        if (is_array($materialsInput) && count($materialsInput) > 0) {
                            $partAggregation = $this->buildUnpricedAggregationFromMaterialsInput($materialsInput, collect());
                        } else {
                            $partAggregation = $this->buildUnpricedAggregationFromBreakdowns((int) $costingData->id, collect());
                        }

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
                                        'detected_price' => $partInfo['detected_price'],
                                        'manual_price' => null,
                                        'notes' => 'Auto-detected from Material Breakdown sync.',
                                    ]
                                );
                            } else {
                                $existingOpen = $openItems->get($partKey);
                                if ($existingOpen) {
                                    $existingOpen->update([
                                        'costing_data_id' => $costingData->id,
                                        'manual_price' => null,
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

            if ($trackingRevisionId && in_array($updateSection, ['', 'resume_cogm'], true)) {
                DocumentRevision::whereKey($trackingRevisionId)->update([
                    'status' => DocumentRevision::STATUS_SUDAH_COSTING,
                ]);
            }

            // Keep database costing total aligned with Material section table total.
            if ($shouldProcessMaterials) {
                $materialCostFromRequest = $this->parseNumericInput($request->input('material_cost', 0));
                $materialCost = $materialCostFromRequest > 0
                    ? $materialCostFromRequest
                    : $this->calculateMaterialCostFromBreakdowns(
                    (int) $costingData->id,
                    (float) ($costingData->exchange_rate_usd ?? 15500),
                    (float) ($costingData->exchange_rate_jpy ?? 103)
                );

                $costingData->update([
                    'material_cost' => $materialCost,
                ]);
            }

            DB::commit();
            $redirectParams = ['id' => $costingData->id];
            if ($trackingRevisionId) {
                $redirectParams['tracking_revision_id'] = $trackingRevisionId;
            }

            $redirectUrl = route('form', $redirectParams, false);
            $sectionLabels = [
                'informasi_project' => 'Informasi Project',
                'rates' => 'Rates',
                'material' => 'Material',
                'unpriced_parts' => 'Rekapan Part Tanpa Harga',
                'cycle_time' => 'Cycle Time',
                'resume_cogm' => 'Resume COGM',
            ];

            $successMessage = $updateSection !== ''
                ? (($sectionLabels[$updateSection] ?? 'Section') . ' berhasil diupdate!')
                : 'Data costing berhasil disimpan!';

            if ($importFromPartlist) {
                $successMessage = 'Partlist berhasil diimport ke Material (' . count($importedMaterialRows) . ' baris).';
            } elseif ($importFromCycleTime) {
                $successMessage = 'Cycle Time berhasil diimport (' . count($importedCycleTimeRows) . ' baris).';
            }

            if ($updateSection === 'unpriced_parts') {
                return redirect($redirectUrl)
                    ->with('success', $successMessage)
                    ->withInput($request->except(['import_partlist_file']));
            }

            session()->flash('success', $successMessage);

            return response('', 302, ['Location' => $redirectUrl]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function importPartlist(Request $request)
    {
        $request->merge([
            'update_section' => 'material',
            'import_partlist' => 1,
        ]);

        return $this->store($request);
    }

    public function importCycleTime(Request $request)
    {
        $request->merge([
            'update_section' => 'cycle_time',
            'import_cycle_time' => 1,
        ]);

        return $this->store($request);
    }

    public function downloadCycleTimeTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cycle Time');

        $sheet->setCellValue('B17', 'No.');
        $sheet->setCellValue('C17', 'Process');
        $sheet->setCellValue('F17', 'Qty');
        $sheet->setCellValue('G17', 'Time (hour)');
        $sheet->setCellValue('I17', 'Area of Process');

        $sampleRows = [
            ['no' => 1, 'process' => 'Cutting, Stripping, Crimping', 'qty' => 120, 'time_hour' => 0.40, 'area' => 'PP - Preparation'],
            ['no' => 2, 'process' => 'Twisting', 'qty' => 120, 'time_hour' => 0.30, 'area' => 'PP - Preparation'],
            ['no' => 3, 'process' => 'HF Sealer', 'qty' => 120, 'time_hour' => 0.25, 'area' => 'FA - Final Assy'],
        ];

        $startRow = 18;
        foreach ($sampleRows as $index => $sample) {
            $row = $startRow + $index;
            $sheet->setCellValue('B' . $row, $sample['no']);
            $sheet->setCellValue('C' . $row, $sample['process']);
            $sheet->setCellValue('F' . $row, $sample['qty']);
            $sheet->setCellValue('G' . $row, $sample['time_hour']);
            $sheet->setCellValue('I' . $row, $sample['area']);
        }

        // Add dropdown validation for Area of Process (I18:I5000).
        for ($row = 18; $row <= 5000; $row++) {
            $validation = $sheet->getCell('I' . $row)->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input tidak valid');
            $validation->setError('Pilih Area of Process: PP - Preparation atau FA - Final Assy.');
            $validation->setPromptTitle('Pilih Area of Process');
            $validation->setPrompt('Gunakan dropdown: PP - Preparation atau FA - Final Assy.');
            $validation->setFormula1('"PP - Preparation,FA - Final Assy"');
        }

        foreach (['A', 'B', 'C', 'F', 'G', 'I'] as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'cycle_time_tpl_');
        if ($tmpPath === false) {
            abort(500, 'Gagal membuat file template sementara.');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return response()->download(
            $tmpPath,
            'cycle-time-template.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function loadPartlistMaterialRows(?int $trackingRevisionId, $uploadedPartlistFile = null): array
    {
        $sourcePath = null;
        $extension = '';

        if ($uploadedPartlistFile) {
            $sourcePath = $uploadedPartlistFile->getPathname();
            $extension = strtolower((string) $uploadedPartlistFile->getClientOriginalExtension());
        } else {
            if (!$trackingRevisionId) {
                return ['rows' => [], 'error' => 'Pilih file partlist terlebih dahulu.'];
            }

            $revision = DocumentRevision::find($trackingRevisionId);
            if (!$revision || empty($revision->partlist_file_path)) {
                return ['rows' => [], 'error' => 'File partlist pada revisi ini tidak tersedia.'];
            }

            if (!Storage::exists($revision->partlist_file_path)) {
                return ['rows' => [], 'error' => 'File partlist tidak ditemukan di storage.'];
            }

            $sourcePath = Storage::path($revision->partlist_file_path);
            $extension = strtolower((string) pathinfo($revision->partlist_file_path, PATHINFO_EXTENSION));
        }

        if (!in_array($extension, ['xlsx', 'xls'], true)) {
            return ['rows' => [], 'error' => 'Format partlist tidak didukung untuk import otomatis.'];
        }

        if (!$sourcePath || !is_readable($sourcePath)) {
            return ['rows' => [], 'error' => 'File partlist tidak dapat diakses oleh server.'];
        }

        $fileSize = @filesize($sourcePath);
        if ($fileSize === false || $fileSize <= 0) {
            return ['rows' => [], 'error' => 'File partlist kosong atau rusak.'];
        }

        try {
            $rows = $this->parsePartlistXlsx((string) $sourcePath);
            if (count($rows) === 0) {
                $diag = $this->diagnosePartlistFile((string) $sourcePath);
                return [
                    'rows' => [],
                    'error' => 'Data partlist tidak terdeteksi dari file. Pastikan data ada di kolom D-J mulai baris 12 (NO di kolom D). ' . $diag,
                ];
            }
            return ['rows' => $rows, 'error' => null];
        } catch (\Throwable $e) {
            return ['rows' => [], 'error' => 'Gagal membaca file partlist: ' . $e->getMessage()];
        }
    }

    private function loadCycleTimeRows($uploadedCycleTimeFile): array
    {
        if (!$uploadedCycleTimeFile) {
            return ['rows' => [], 'error' => 'File Cycle Time belum dipilih.'];
        }

        $sourcePath = $uploadedCycleTimeFile->getPathname();
        $extension = strtolower((string) $uploadedCycleTimeFile->getClientOriginalExtension());

        if (!in_array($extension, ['xlsx', 'xls'], true)) {
            return ['rows' => [], 'error' => 'Format file Cycle Time tidak didukung untuk import otomatis.'];
        }

        if (!$sourcePath || !is_readable($sourcePath)) {
            return ['rows' => [], 'error' => 'File Cycle Time tidak dapat diakses oleh server.'];
        }

        $fileSize = @filesize($sourcePath);
        if ($fileSize === false || $fileSize <= 0) {
            return ['rows' => [], 'error' => 'File Cycle Time kosong atau rusak.'];
        }

        try {
            $rows = $this->parseCycleTimeXlsx((string) $sourcePath);
            return ['rows' => $rows, 'error' => null];
        } catch (\Throwable $e) {
            return ['rows' => [], 'error' => 'Gagal membaca file Cycle Time: ' . $e->getMessage()];
        }
    }

    private function parseCycleTimeXlsx(string $filePath): array
    {
        if (!class_exists(IOFactory::class)) {
            throw new \RuntimeException('Parser PhpSpreadsheet tidak tersedia.');
        }

        $spreadsheet = IOFactory::load($filePath);
        $bestCycleTimes = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if (!$sheet instanceof Worksheet) {
                continue;
            }

            $rows = $this->extractCycleTimesFromTemplateSheet($sheet);
            if (count($rows) > count($bestCycleTimes)) {
                $bestCycleTimes = $rows;
            }
        }

        return $bestCycleTimes;
    }

    private function extractCycleTimesFromTemplateSheet(Worksheet $sheet): array
    {
        $highestDataRow = (int) $sheet->getHighestDataRow();
        $highestRow = (int) $sheet->getHighestRow();

        $scanEnd = max($highestDataRow, 18);
        if ($scanEnd < 18 && $highestRow >= 18) {
            $scanEnd = min($highestRow, 5000);
        } else {
            $scanEnd = min(max($scanEnd + 120, 250), 5000);
        }

        $cycleTimes = [];
        $emptyStreak = 0;

        for ($row = 18; $row <= $scanEnd; $row++) {
            $noRaw = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());
            $process = trim((string) $sheet->getCell('C' . $row)->getFormattedValue());
            $qtyRaw = trim((string) $sheet->getCell('F' . $row)->getFormattedValue());
            $timeHourRaw = trim((string) $sheet->getCell('G' . $row)->getFormattedValue());
            $areaRaw = trim((string) $sheet->getCell('I' . $row)->getFormattedValue());

            $hasSignal = $noRaw !== ''
                || $process !== ''
                || $qtyRaw !== ''
                || $timeHourRaw !== ''
                || $areaRaw !== '';

            if (!$hasSignal) {
                $emptyStreak++;
                if ($emptyStreak >= 60) {
                    break;
                }
                continue;
            }

            $emptyStreak = 0;

            if ($process === '') {
                continue;
            }

            $rowData = [
                'process' => $process,
                'qty' => $qtyRaw !== '' ? $this->toFloatValue($qtyRaw) : null,
                'time_hour' => $timeHourRaw !== '' ? $this->toFloatValue($timeHourRaw) : null,
                'time_sec' => null,
                'time_sec_per_qty' => null,
                'cost_per_sec' => 10.33,
                'cost_per_unit' => null,
                'area_of_process' => $this->normalizeAreaOfProcess($areaRaw),
            ];

            // Qty only applied when process (column C) exists.
            if ($rowData['process'] === '') {
                $rowData['qty'] = null;
            }

            $cycleTimes[] = $rowData;
        }

        return $cycleTimes;
    }

    private function normalizeAreaOfProcess(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if ($raw === 'PP - Preparation' || $raw === 'FA - Final Assy') {
            return $raw;
        }

        $normalized = strtoupper(preg_replace('/\s+/', ' ', $raw));

        if (in_array($normalized, ['PP', 'PREPARATION', 'PP PREPARATION', 'PP - PREPARATION'], true)) {
            return 'PP - Preparation';
        }

        if (in_array($normalized, ['FA', 'FINAL ASSY', 'FINAL ASSY', 'FA FINAL ASSY', 'FA - FINAL ASSY'], true)) {
            return 'FA - Final Assy';
        }

        return null;
    }

    private function buildUnpricedAggregationFromBreakdowns(int $costingDataId, $manualUnpricedPrices): array
    {
        $rows = MaterialBreakdown::with('material')
            ->where('costing_data_id', $costingDataId)
            ->get();

        $aggregation = [];

        foreach ($rows as $row) {
            $partNumber = trim((string) ($row->part_no ?? ''));
            if ($partNumber === '' || $partNumber === '-') {
                continue;
            }

            $partName = trim((string) ($row->part_name ?? ''));

            $partKey = strtolower($partNumber);
            $manualPrice = floatval($manualUnpricedPrices->get($partKey, 0));
            $detectedPrice = floatval($row->material->price ?? 0);
            $rowAmount1 = floatval($row->amount1 ?? 0);
            $rowBasisPrice = floatval($row->unit_price_basis ?? 0);
            $isUnpriced = ($rowAmount1 <= 0)
                && ($rowBasisPrice <= 0)
                && ($manualPrice <= 0);

            if (!isset($aggregation[$partKey])) {
                $aggregation[$partKey] = [
                    'part_number' => $partNumber,
                    'part_name' => $partName,
                    'detected_price' => $detectedPrice,
                    'manual_price' => $manualPrice > 0 ? $manualPrice : null,
                    'is_unpriced' => false,
                ];
            }

            $aggregation[$partKey]['is_unpriced'] = $aggregation[$partKey]['is_unpriced'] || $isUnpriced;

            if ($manualPrice > 0) {
                $aggregation[$partKey]['manual_price'] = $manualPrice;
            }
        }

        return $aggregation;
    }

    private function buildUnpricedAggregationFromMaterialsInput(array $materialsInput, $manualUnpricedPrices): array
    {
        $aggregation = [];

        foreach ($materialsInput as $matData) {
            $partNo = trim((string) ($matData['part_no'] ?? ''));
            if ($partNo === '' || $partNo === '-') {
                continue;
            }

            $partKey = strtolower($partNo);
            $partName = trim((string) ($matData['part_name'] ?? ''));

            $qtyReq = intval(round(floatval($matData['qty_req'] ?? 0)));
            $amount1 = $this->toFloatValue($matData['amount1'] ?? 0);
            $unitPriceBasisRaw = trim((string) ($matData['unit_price_basis_text'] ?? $matData['unit_price_basis'] ?? ''));
            $unitPriceBasis = $this->toFloatValue($unitPriceBasisRaw);
            $manualPrice = floatval($manualUnpricedPrices->get($partKey, 0));

            $rowPartNo = trim((string) ($matData['part_no'] ?? ''));
            $rowIdCode = trim((string) ($matData['id_code'] ?? ''));
            $masterMaterial = $this->findMasterMaterial($rowPartNo, $rowIdCode);
            $detectedPrice = floatval($masterMaterial?->price ?? 0);

            $isUnpriced = ($amount1 <= 0)
                && ($unitPriceBasis <= 0)
                && ($manualPrice <= 0);

            if (!isset($aggregation[$partKey])) {
                $aggregation[$partKey] = [
                    'part_number' => $partNo,
                    'part_name' => $partName,
                    'detected_price' => $detectedPrice,
                    'manual_price' => $manualPrice > 0 ? $manualPrice : null,
                    'is_unpriced' => false,
                ];
            }

            $aggregation[$partKey]['is_unpriced'] = $aggregation[$partKey]['is_unpriced'] || $isUnpriced;

            if ($manualPrice > 0) {
                $aggregation[$partKey]['manual_price'] = $manualPrice;
            }
        }

        return $aggregation;
    }

    private function parsePartlistXlsx(string $filePath): array
    {
        if (class_exists(IOFactory::class)) {
            $rows = $this->parsePartlistWithPhpSpreadsheet($filePath);
            if (count($rows) > 0) {
                return $rows;
            }
        }

        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('Ekstensi PHP zip belum aktif. Aktifkan ext-zip untuk import partlist XLSX.');
        }

        $zip = new ZipArchive();
        $tempCopyPath = null;
        $zipOpenResult = $zip->open($filePath);
        if ($zipOpenResult !== true) {
            $tempCopyPath = tempnam(sys_get_temp_dir(), 'partlist_');
            if ($tempCopyPath && @copy($filePath, $tempCopyPath)) {
                $retryZip = new ZipArchive();
                $retryResult = $retryZip->open($tempCopyPath);
                if ($retryResult === true) {
                    $zip = $retryZip;
                } else {
                    @unlink($tempCopyPath);
                    throw new \RuntimeException('File Excel tidak dapat dibuka (' . $this->zipOpenErrorToMessage((int) $retryResult) . ').');
                }
            } else {
                throw new \RuntimeException('File Excel tidak dapat dibuka (' . $this->zipOpenErrorToMessage((int) $zipOpenResult) . ').');
            }
        }

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $workbookRelsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml === false || $workbookRelsXml === false) {
            $zip->close();
            throw new \RuntimeException('Struktur workbook tidak valid.');
        }

        $workbook = @simplexml_load_string($workbookXml);
        $rels = @simplexml_load_string($workbookRelsXml);
        if (!$workbook || !$rels) {
            $zip->close();
            throw new \RuntimeException('Workbook tidak dapat diparse.');
        }

        $relNs = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $relationshipTargets = [];
        foreach ($rels->Relationship as $relationship) {
            $relationshipTargets[(string) ($relationship['Id'] ?? '')] = (string) ($relationship['Target'] ?? '');
        }

        $sheetTargets = [];
        if (isset($workbook->sheets->sheet)) {
            foreach ($workbook->sheets->sheet as $sheetNode) {
                $sheetAttrs = $sheetNode->attributes($relNs);
                $sheetRid = (string) ($sheetAttrs['id'] ?? '');
                if ($sheetRid === '' || empty($relationshipTargets[$sheetRid])) {
                    continue;
                }

                $sheetTargets[] = (string) $relationshipTargets[$sheetRid];
            }
        }

        if (count($sheetTargets) === 0) {
            $zip->close();
            throw new \RuntimeException('Sheet partlist tidak ditemukan.');
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $sharedStringsDoc = @simplexml_load_string($sharedStringsXml);
            if ($sharedStringsDoc && isset($sharedStringsDoc->si)) {
                foreach ($sharedStringsDoc->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = trim((string) $si->t);
                        continue;
                    }

                    $text = '';
                    foreach ($si->r as $run) {
                        $text .= (string) ($run->t ?? '');
                    }
                    $sharedStrings[] = trim($text);
                }
            }
        }

        $bestRows = [];
        $bestCount = 0;

        foreach ($sheetTargets as $sheetTarget) {
            $sheetPath = 'xl/' . ltrim((string) $sheetTarget, '/');
            $sheetXml = $zip->getFromName($sheetPath);
            if ($sheetXml === false) {
                continue;
            }

            $sheet = @simplexml_load_string($sheetXml);
            if (!$sheet || !isset($sheet->sheetData->row)) {
                continue;
            }

            $rawRows = [];
            foreach ($sheet->sheetData->row as $row) {
                $rowNumber = (int) ($row['r'] ?? 0);
                $rowValues = [];
                foreach ($row->c as $cell) {
                    $cellRef = (string) ($cell['r'] ?? '');
                    $columnRef = preg_replace('/\d+/', '', $cellRef);
                    if ($columnRef === '') {
                        continue;
                    }

                    $columnIndex = $this->excelColumnToIndex($columnRef);
                    $value = $this->extractXlsxCellValue($cell, $sharedStrings);

                    $rowValues[$columnIndex] = $value;
                }

                if (!empty($rowValues)) {
                    $rowValues['__row'] = $rowNumber;
                    $rawRows[] = $rowValues;
                }
            }

            if (count($rawRows) === 0) {
                continue;
            }

            $mappedRows = $this->mapPartlistRowsToMaterials($rawRows);
            if (count($mappedRows) > $bestCount) {
                $bestRows = $mappedRows;
                $bestCount = count($mappedRows);
            }
        }

        $zip->close();
        if ($tempCopyPath) {
            @unlink($tempCopyPath);
        }

        if ($bestCount === 0) {
            return [];
        }

        return $bestRows;
    }

    private function parsePartlistWithPhpSpreadsheet(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (\Throwable $e) {
            return [];
        }

        $bestMaterials = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if (!$sheet instanceof Worksheet) {
                continue;
            }

            $materials = $this->extractMaterialsFromFixedTemplateSheet($sheet);
            if (count($materials) > count($bestMaterials)) {
                $bestMaterials = $materials;
            }
        }

        // Fallback: read fixed template columns directly (D-J from row 12)
        // for files where header labels are missing/shifted but data rows exist.
        if (count($bestMaterials) === 0) {
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                if (!$sheet instanceof Worksheet) {
                    continue;
                }

                $materials = $this->extractMaterialsFromLooseFixedColumnsSheet($sheet);
                if (count($materials) > count($bestMaterials)) {
                    $bestMaterials = $materials;
                }
            }
        }

        return $bestMaterials;
    }

    private function extractMaterialsFromLooseFixedColumnsSheet(Worksheet $sheet): array
    {
        $highestDataRow = (int) $sheet->getHighestDataRow();
        $highestRow = (int) $sheet->getHighestRow();

        $scanEnd = max($highestDataRow, 12);
        if ($scanEnd < 12 && $highestRow >= 12) {
            $scanEnd = min($highestRow, 5000);
        } else {
            $scanEnd = min(max($scanEnd + 200, 200), 5000);
        }

        $skipPartNos = [
            'NO ASSY',
            'ASSY NAME',
            'CUSTOMER',
            'MODEL',
            'TANGGAL',
            'PIC ENGINEERING',
            'PIC MARKETING',
            'PART NO',
            'SUPPLIER PART NO',
            'ID CODE',
            'PART NAME',
            'QTY',
            'UNIT',
            'PRO CODE',
            'NO',
            'NO.',
            'NOMOR',
        ];

        $materials = [];
        $emptyStreak = 0;

        for ($row = 12; $row <= $scanEnd; $row++) {
            $rowNo = trim((string) $sheet->getCell('D' . $row)->getFormattedValue());
            $partNo = trim((string) $sheet->getCell('E' . $row)->getFormattedValue());
            $idCode = trim((string) $sheet->getCell('F' . $row)->getFormattedValue());
            $partName = trim((string) $sheet->getCell('G' . $row)->getFormattedValue());
            $qtyRaw = trim((string) $sheet->getCell('H' . $row)->getFormattedValue());
            $unit = trim((string) $sheet->getCell('I' . $row)->getFormattedValue());
            $proCode = trim((string) $sheet->getCell('J' . $row)->getFormattedValue());

            $qtyReq = $this->toFloatValue($qtyRaw);
            $hasRowNumber = $this->hasPartlistRowNumber($rowNo);

            if ($partNo === '' || $partNo === '-') {
                $partNo = $idCode;
            }

            $hasSignalData = ($partNo !== '' && $partNo !== '-')
                || ($idCode !== '' && $idCode !== '-')
                || ($partName !== '' && $partName !== '-')
                || $qtyReq > 0
                || $proCode !== '';

            if (!$hasRowNumber && !$hasSignalData) {
                $emptyStreak++;
                if ($emptyStreak >= 80) {
                    break;
                }
                continue;
            }

            $emptyStreak = 0;

            // Primary requirement: row count follows NO column (D12+).
            if (!$hasRowNumber) {
                continue;
            }

            $partNoUpper = strtoupper($partNo);
            $idCodeUpper = strtoupper($idCode);
            $partNameUpper = strtoupper($partName);
            if (in_array($partNoUpper, $skipPartNos, true)
                || in_array($idCodeUpper, $skipPartNos, true)
                || in_array($partNameUpper, $skipPartNos, true)) {
                continue;
            }

            $materials[] = [
                'row_no' => $rowNo,
                'part_no' => $partNo,
                'id_code' => $idCode !== '' && $idCode !== '-' ? $idCode : null,
                'part_name' => $partName,
                'qty_req' => $qtyReq,
                'unit' => $this->normalizeUnitValue($unit),
                'pro_code' => $proCode,
                'amount1' => 0,
                'unit_price_basis' => 0,
                'unit_price_basis_text' => null,
                'currency' => 'IDR',
                'qty_moq' => 0,
                'cn_type' => 'N',
                'supplier' => '',
                'import_tax' => 0,
            ];
        }

        return $materials;
    }

    private function extractMaterialsFromFixedTemplateSheet(Worksheet $sheet): array
    {
        $highestRow = (int) $sheet->getHighestDataRow();
        if ($highestRow < 12) {
            return [];
        }

        // Find header row (expect row 11) and map column indices dynamically
        $headerRowIndex = 11;
        $headerMap = [];
        
        $headerLabels = [
            'row_no' => ['NO', 'NO.', 'NOMOR'],
            'supplier_part_no' => ['SUPPLIER PART NO', 'PART NO', 'PARTLIST NO'],
            'id_code' => ['ID CODE', 'ID', 'KODE ID'],
            'part_name' => ['PART NAME', 'NAMA PART', 'DESKRIPSI'],
            'qty_req' => ['Q', 'QTY', 'QUANTITY', 'Q/ASSY'],
            'unit' => ['UNIT', 'UOM', 'SATUAN'],
            'pro_code' => ['PRO CODE', 'PROSES', 'PROCESS CODE'],
        ];

        // Scan row 11 to find headers
        for ($col = 1; $col <= 20; $col++) {
            $cellValue = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($col) . $headerRowIndex)->getFormattedValue());
            $cellValueUpper = strtoupper($cellValue);
            
            foreach ($headerLabels as $key => $aliases) {
                if (in_array($cellValueUpper, $aliases, true)) {
                    $headerMap[$key] = $col;
                    break;
                }
            }
        }

        // If we couldn't find headers, fallback to default columns (E-J)
        if (empty($headerMap)) {
            $headerMap = [
            'row_no' => 4,            // D
                'supplier_part_no' => 5,  // E
                'id_code' => 6,            // F
                'part_name' => 7,          // G
                'qty_req' => 8,            // H
                'unit' => 9,               // I
                'pro_code' => 10,          // J
            ];
        }

        $skipPartNos = [
            'NO ASSY',
            'ASSY NAME',
            'CUSTOMER',
            'MODEL',
            'TANGGAL',
            'PIC ENGINEERING',
            'PIC MARKETING',
            'PART NO',
            'SUPPLIER PART NO',
            'ID CODE',
            'PART NAME',
            'QTY',
            'UNIT',
            'PRO CODE',
        ];

        $materials = [];
        for ($row = 12; $row <= $highestRow; $row++) {
            $rowNo = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($headerMap['row_no'] ?? 4) . $row)->getFormattedValue());
            $partNo = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($headerMap['supplier_part_no'] ?? 5) . $row)->getFormattedValue());
            
            // Only read ID CODE if header was explicitly found, otherwise keep empty
            $idCode = '';
            if (isset($headerMap['id_code'])) {
                $idCode = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($headerMap['id_code']) . $row)->getFormattedValue());
            }
            
            $partName = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($headerMap['part_name'] ?? 7) . $row)->getFormattedValue());
            $qtyRaw = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($headerMap['qty_req'] ?? 8) . $row)->getFormattedValue());
            $unit = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($headerMap['unit'] ?? 9) . $row)->getFormattedValue());
            $proCode = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($headerMap['pro_code'] ?? 10) . $row)->getFormattedValue());

            if ($partNo === '' || $partNo === '-') {
                $partNo = $idCode;
            }

            $qtyReq = intval(round($this->toFloatValue($qtyRaw)));
            $hasRowNumber = $this->hasPartlistRowNumber($rowNo);

            $isRowEmpty = ($partNo === '' || $partNo === '-')
                && ($idCode === '' || $idCode === '-')
                && $partName === ''
                && $qtyReq <= 0
                && $proCode === '';

            if ($isRowEmpty && !$hasRowNumber) {
                continue;
            }

            $partNoUpper = strtoupper($partNo);
            $idCodeUpper = strtoupper($idCode);
            $partNameUpper = strtoupper($partName);
            if (in_array($partNoUpper, $skipPartNos, true)
                || in_array($idCodeUpper, $skipPartNos, true)
                || in_array($partNameUpper, $skipPartNos, true)) {
                continue;
            }

            $materials[] = [
                'row_no' => $rowNo,
                'part_no' => $partNo,
                'id_code' => $idCode !== '' && $idCode !== '-' ? $idCode : null,
                'part_name' => $partName,
                'qty_req' => $qtyReq,
                'unit' => $this->normalizeUnitValue($unit),
                'pro_code' => $proCode,
                'amount1' => 0,
                'unit_price_basis' => 0,
                'unit_price_basis_text' => null,
                'currency' => 'IDR',
                'qty_moq' => 0,
                'cn_type' => 'N',
                'supplier' => '',
                'import_tax' => 0,
            ];
        }

        return $materials;
    }

    private function mapPartlistRowsToMaterials(array $rawRows): array
    {
        // Primary rule requested: fixed template columns D:J starting row 12.
        $fixedTemplateRows = $this->mapPartlistRowsByFixedTemplate($rawRows);
        if (count($fixedTemplateRows) > 0) {
            return $fixedTemplateRows;
        }

        $headerRowIndex = null;
        $headerMap = [];

        foreach ($rawRows as $rowIndex => $rowValues) {
            $candidate = [];
            foreach ($rowValues as $columnIndex => $rawValue) {
                if (!is_int($columnIndex)) {
                    continue;
                }

                $headerKey = $this->mapPartlistHeader((string) $rawValue);
                if ($headerKey !== null && !isset($candidate[$headerKey])) {
                    $candidate[$headerKey] = $columnIndex;
                }
            }

            if ((isset($candidate['part_no']) || isset($candidate['id_code']))
                && (isset($candidate['part_name']) || isset($candidate['qty_req']) || isset($candidate['unit']))) {
                $headerRowIndex = $rowIndex;
                $headerMap = $candidate;
                break;
            }
        }

        if ($headerRowIndex === null) {
            return $this->mapPartlistRowsByFixedTemplate($rawRows);
        }

        $materials = [];
        foreach (array_slice($rawRows, $headerRowIndex + 1) as $rowValues) {
            $rowNo = trim((string) $this->rowCellValue($rowValues, $headerMap, 'row_no'));
            $partNo = trim((string) $this->rowCellValue($rowValues, $headerMap, 'part_no'));
            $idCode = trim((string) $this->rowCellValue($rowValues, $headerMap, 'id_code'));
            if ($partNo === '' || $partNo === '-') {
                $partNo = $idCode;
            }

            $partName = trim((string) $this->rowCellValue($rowValues, $headerMap, 'part_name'));
            $qtyReq = intval(round($this->toFloatValue($this->rowCellValue($rowValues, $headerMap, 'qty_req'))));

            if (($partNo === '' || $partNo === '-') && ($idCode === '' || $idCode === '-') && $partName === '' && $qtyReq <= 0) {
                continue;
            }

            $unit = trim((string) $this->rowCellValue($rowValues, $headerMap, 'unit'));
            $currency = strtoupper(trim((string) $this->rowCellValue($rowValues, $headerMap, 'currency')));
            $cnType = strtoupper(trim((string) $this->rowCellValue($rowValues, $headerMap, 'cn_type')));

            $materials[] = [
                'row_no' => $rowNo,
                'part_no' => $partNo,
                'id_code' => ($idCode !== '' && $idCode !== '-') ? $idCode : null,
                'part_name' => $partName,
                'qty_req' => $qtyReq,
                'unit' => $this->normalizeUnitValue($unit),
                'pro_code' => trim((string) $this->rowCellValue($rowValues, $headerMap, 'pro_code')),
                // Keep price fields empty for partlist import; users fill via manual input or unpriced recap action.
                'amount1' => 0,
                'unit_price_basis' => 0,
                'unit_price_basis_text' => null,
                'currency' => in_array($currency, ['IDR', 'USD', 'JPY'], true) ? $currency : 'IDR',
                'qty_moq' => $this->toFloatValue($this->rowCellValue($rowValues, $headerMap, 'qty_moq')),
                'cn_type' => in_array($cnType, ['C', 'N', 'E'], true) ? $cnType : 'N',
                'supplier' => trim((string) $this->rowCellValue($rowValues, $headerMap, 'supplier')),
                'import_tax' => $this->toFloatValue($this->rowCellValue($rowValues, $headerMap, 'import_tax')),
            ];
        }

        return $materials;
    }

    private function mapPartlistRowsByFixedTemplate(array $rawRows): array
    {
        $skipPartNos = [
            'NO ASSY',
            'ASSY NAME',
            'CUSTOMER',
            'MODEL',
            'TANGGAL',
            'PIC ENGINEERING',
            'PIC MARKETING',
            'PART NO',
        ];

        // Find header row (row 11) and detect column mapping dynamically
        $headerRow = null;
        foreach ($rawRows as $rowValues) {
            $rowNumber = (int) ($rowValues['__row'] ?? 0);
            if ($rowNumber === 11) {
                $headerRow = $rowValues;
                break;
            }
        }

        $headerMap = [
            'row_no' => 3,              // Default D
            'supplier_part_no' => 4,  // Default E
            'id_code' => 5,            // Default F
            'part_name' => 6,          // Default G
            'qty_req' => 7,            // Default H
            'unit' => 8,               // Default I
            'pro_code' => 9,           // Default J
        ];

        // If we found header row, try to dynamically map columns
        if ($headerRow) {
            $headerLabels = [
                'row_no' => ['NO', 'NO.', 'NOMOR'],
                'supplier_part_no' => ['SUPPLIER PART NO', 'PART NO', 'PARTLIST NO'],
                'id_code' => ['ID CODE', 'ID', 'KODE ID'],
                'part_name' => ['PART NAME', 'NAMA PART', 'DESKRIPSI'],
                'qty_req' => ['Q', 'QTY', 'QUANTITY', 'Q/ASSY'],
                'unit' => ['UNIT', 'UOM', 'SATUAN'],
                'pro_code' => ['PRO CODE', 'PROSES', 'PROCESS CODE'],
            ];

            foreach ($headerRow as $colIndex => $cellValue) {
                if (!is_int($colIndex)) continue;
                
                $headerValueUpper = strtoupper(trim((string) $cellValue));
                foreach ($headerLabels as $field => $aliases) {
                    if (in_array($headerValueUpper, $aliases, true)) {
                        $headerMap[$field] = $colIndex;
                        break;
                    }
                }
            }
        }

        // Filter to data rows (12+)
        $rows = array_values(array_filter($rawRows, function ($rowValues) {
            $rowNumber = (int) ($rowValues['__row'] ?? 0);
            return $rowNumber >= 12;
        }));

        $materials = [];
        foreach ($rows as $rowValues) {
            $rowNo = trim((string) ($rowValues[$headerMap['row_no']] ?? ''));
            $partNo = trim((string) ($rowValues[$headerMap['supplier_part_no']] ?? ''));
            
            // Only read ID CODE if header was explicitly found
            $idCode = '';
            if (isset($headerMap['id_code'])) {
                $idCode = trim((string) ($rowValues[$headerMap['id_code']] ?? ''));
            }
            
            if ($partNo === '' || $partNo === '-') {
                $partNo = $idCode;
            }

            $partName = trim((string) ($rowValues[$headerMap['part_name']] ?? ''));
            $partNoUpper = strtoupper($partNo);
            $idCodeUpper = strtoupper($idCode);
            $qtyReq = intval(round($this->toFloatValue($rowValues[$headerMap['qty_req']] ?? '')));
            $hasRowNumber = $this->hasPartlistRowNumber($rowNo);

            $isRowEmpty = ($partNo === '' || $partNo === '-')
                && ($idCode === '' || $idCode === '-')
                && $partName === ''
                && $qtyReq <= 0;

            $isHeaderLike = in_array($partNoUpper, $skipPartNos, true)
                || in_array($idCodeUpper, $skipPartNos, true)
                || in_array(strtoupper($partName), $skipPartNos, true);

            if (($isRowEmpty && !$hasRowNumber) || $isHeaderLike) {
                continue;
            }

            $materials[] = [
                'row_no' => $rowNo,
                'part_no' => $partNo,
                'id_code' => ($idCode !== '' && $idCode !== '-') ? $idCode : null,
                'part_name' => $partName,
                'qty_req' => $qtyReq,
                'unit' => $this->normalizeUnitValue($rowValues[$headerMap['unit']] ?? ''),
                'pro_code' => trim((string) ($rowValues[$headerMap['pro_code']] ?? '')),
                'amount1' => 0,
                'unit_price_basis' => 0,
                'unit_price_basis_text' => null,
                'currency' => 'IDR',
                'qty_moq' => 0,
                'cn_type' => 'N',
                'supplier' => '',
                'import_tax' => 0,
            ];
        }

        return $materials;
    }

    private function hasPartlistRowNumber(string $value): bool
    {
        $normalized = strtoupper(trim($value));
        if ($normalized === '') {
            return false;
        }

        return !in_array($normalized, ['NO', 'NO.', 'NOMOR'], true);
    }

    private function mapPartlistHeader(string $value): ?string
    {
        $normalized = preg_replace('/[^a-z0-9]/', '', strtolower(trim($value)));
        if ($normalized === '') {
            return null;
        }

        $headerAliases = [
            'row_no' => ['no', 'nomor', 'rownumber', 'rowno', 'itemno', 'nomorurut', 'urut'],
            'part_no' => ['partno', 'partnumber', 'materialcode', 'partnumbermaterial', 'pn', 'partnumberno'],
            'id_code' => ['idcode', 'idmaterial', 'materialid', 'itemcode', 'id', 'kodepart', 'code'],
            'part_name' => ['partname', 'materialdescription', 'description', 'namapart'],
            'qty_req' => ['qtyreq', 'qtyrequired', 'qty', 'usageqty', 'qtyperassy', 'quantity', 'qtyneed', 'qtypcs'],
            'unit' => ['unit', 'uom', 'baseuom'],
            'pro_code' => ['procode', 'processcode', 'kodeproses', 'proc', 'process'],
            'amount1' => ['amount1', 'price', 'hargasatuan', 'materialprice'],
            'unit_price_basis' => ['unitpricebasis', 'basisprice', 'unitprice', 'pricebasis'],
            'currency' => ['currency', 'curr', 'matauang'],
            'qty_moq' => ['qtymoq', 'moq', 'minimumorderqty'],
            'cn_type' => ['cn', 'ctype', 'cntype', 'cndesc'],
            'supplier' => ['supplier', 'vendor', 'maker'],
            'import_tax' => ['importtax', 'importtaxpercent', 'taximport'],
        ];

        foreach ($headerAliases as $field => $aliases) {
            if (in_array($normalized, $aliases, true)) {
                return $field;
            }
        }

        return null;
    }

    private function rowCellValue(array $rowValues, array $headerMap, string $field): string
    {
        if (!isset($headerMap[$field])) {
            return '';
        }

        $columnIndex = $headerMap[$field];
        return isset($rowValues[$columnIndex]) ? (string) $rowValues[$columnIndex] : '';
    }

    private function extractXlsxCellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) ($cell['t'] ?? '');

        if ($type === 's') {
            $sharedIndex = (int) ($cell->v ?? 0);
            return trim((string) ($sharedStrings[$sharedIndex] ?? ''));
        }

        if ($type === 'inlineStr') {
            if (isset($cell->is->t)) {
                return trim((string) $cell->is->t);
            }

            $richText = '';
            foreach ($cell->is->r as $run) {
                $richText .= (string) ($run->t ?? '');
            }

            return trim($richText);
        }

        if (isset($cell->v)) {
            return trim((string) $cell->v);
        }

        if (isset($cell->is->t)) {
            return trim((string) $cell->is->t);
        }

        if (isset($cell->f)) {
            // Fallback: if formula has no cached value, keep formula text instead of empty.
            return trim((string) $cell->f);
        }

        return '';
    }

    private function excelColumnToIndex(string $columnRef): int
    {
        $columnRef = strtoupper($columnRef);
        $index = 0;
        $length = strlen($columnRef);

        for ($i = 0; $i < $length; $i++) {
            $index = ($index * 26) + (ord($columnRef[$i]) - 64);
        }

        return $index - 1;
    }

    private function toFloatValue($value): float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return 0;
        }

        $normalized = preg_replace('/[^0-9,\.\-]/', '', $raw);
        if ($normalized === '' || $normalized === '-' || $normalized === '.' || $normalized === ',') {
            return 0;
        }

        $hasComma = str_contains($normalized, ',');
        $hasDot = str_contains($normalized, '.');

        if ($hasComma && $hasDot) {
            $lastCommaPos = strrpos($normalized, ',');
            $lastDotPos = strrpos($normalized, '.');

            if ($lastCommaPos !== false && $lastDotPos !== false && $lastCommaPos > $lastDotPos) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($hasComma && !$hasDot) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return is_numeric($normalized) ? (float) $normalized : 0;
    }

    private function parseNumericInput($value): float
    {
        return $this->toFloatValue($value);
    }

    private function calculateMaterialCostFromBreakdowns(int $costingDataId, float $usdRate, float $jpyRate): float
    {
        $usdRate = $usdRate > 0 ? $usdRate : 15500;
        $jpyRate = $jpyRate > 0 ? $jpyRate : 103;

        $rows = MaterialBreakdown::where('costing_data_id', $costingDataId)
            ->get(['qty_req', 'amount2', 'currency']);

        $total = 0.0;
        foreach ($rows as $row) {
            $qtyReq = max(0.0, (float) ($row->qty_req ?? 0));
            $amount2 = max(0.0, (float) ($row->amount2 ?? 0));
            $currency = strtoupper(trim((string) ($row->currency ?? 'IDR')));

            $rate = match ($currency) {
                'USD' => $usdRate,
                'JPY' => $jpyRate,
                default => 1.0,
            };

            $total += $qtyReq * $amount2 * $rate;
        }

        return round($total, 4);
    }

    private function uploadErrorCodeToMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'ukuran file melebihi batas upload_max_filesize server',
            UPLOAD_ERR_FORM_SIZE => 'ukuran file melebihi batas form',
            UPLOAD_ERR_PARTIAL => 'file hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'tidak ada file yang dipilih',
            UPLOAD_ERR_NO_TMP_DIR => 'folder temporary upload tidak tersedia',
            UPLOAD_ERR_CANT_WRITE => 'server gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'upload dibatalkan oleh ekstensi PHP',
            default => 'error upload tidak diketahui',
        };
    }

    private function zipOpenErrorToMessage(int $zipErrorCode): string
    {
        return match ($zipErrorCode) {
            ZipArchive::ER_NOZIP => 'format file bukan ZIP/XLSX yang valid',
            ZipArchive::ER_INCONS => 'struktur ZIP/XLSX tidak konsisten',
            ZipArchive::ER_READ => 'gagal membaca file',
            ZipArchive::ER_OPEN => 'file tidak bisa dibuka',
            ZipArchive::ER_NOENT => 'file tidak ditemukan',
            default => 'kode error ZIP: ' . $zipErrorCode,
        };
    }

    private function diagnosePartlistFile(string $filePath): string
    {
        if (!class_exists(IOFactory::class)) {
            return 'Parser PhpSpreadsheet tidak tersedia.';
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (\Throwable $e) {
            return 'File terbaca tetapi gagal didiagnosa: ' . $e->getMessage();
        }

        $summary = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if (!$sheet instanceof Worksheet) {
                continue;
            }

            $highestRow = (int) $sheet->getHighestDataRow();
            $candidates = 0;
            for ($r = 12; $r <= $highestRow; $r++) {
                $partNo = trim((string) $sheet->getCell('E' . $r)->getFormattedValue());
                $idCode = trim((string) $sheet->getCell('F' . $r)->getFormattedValue());
                $partName = trim((string) $sheet->getCell('G' . $r)->getFormattedValue());
                $qtyRaw = trim((string) $sheet->getCell('H' . $r)->getFormattedValue());
                $proCode = trim((string) $sheet->getCell('J' . $r)->getFormattedValue());
                $qtyReq = $this->toFloatValue($qtyRaw);

                $hasData = ($partNo !== '' && $partNo !== '-')
                    || ($idCode !== '' && $idCode !== '-')
                    || $partName !== ''
                    || $qtyReq > 0
                    || ($proCode !== '' && $proCode !== '-');

                if ($hasData) {
                    $candidates++;
                }
            }

            $summary[] = $sheet->getTitle() . ': rowData=' . $candidates . ', highestRow=' . $highestRow;
        }

        if (count($summary) === 0) {
            return 'Workbook tidak memiliki sheet yang dapat dibaca.';
        }

        return 'Diagnosa sheet -> ' . implode(' | ', $summary);
    }

    private function normalizeUnitValue($value): string
    {
        $unit = strtoupper(trim((string) $value));

        if ($unit === '') {
            return 'PCS';
        }

        return $unit;
    }

    private function validMasterMaterialsQuery()
    {
        $skipCodes = $this->materialMetaSkipCodes();

        return Material::query()
            ->whereNotNull('material_code')
            ->where('material_code', '!=', '')
            ->where('material_code', '!=', '-')
            ->where('material_code', 'not like', '__ROW_%')
            ->whereRaw('UPPER(material_code) NOT IN (' . implode(',', array_fill(0, count($skipCodes), '?')) . ')', $skipCodes);
    }

    private function findMasterMaterial(?string $partNo, ?string $idCode): ?Material
    {
        $partNo = trim((string) $partNo);
        $idCode = trim((string) $idCode);

        $candidates = array_values(array_unique(array_filter([$partNo, $idCode], function ($value) {
            $normalized = trim((string) $value);
            return $normalized !== '' && $normalized !== '-' && !$this->isMaterialMetaCode($normalized);
        })));

        if (empty($candidates)) {
            return null;
        }

        foreach ($candidates as $code) {
            $match = $this->validMasterMaterialsQuery()
                ->whereRaw('LOWER(material_code) = ?', [Str::lower($code)])
                ->first();

            if ($match) {
                return $match;
            }
        }

        return null;
    }

    private function materialMetaSkipCodes(): array
    {
        return [
            'NO ASSY',
            'ASSY NAME',
            'CUSTOMER',
            'MODEL',
            'TANGGAL',
            'PIC ENGINEERING',
            'PIC MARKETING',
            'SUPPLIER PART NO',
            'PART NO',
            'ID CODE',
            'PART NAME',
            'QTY',
            'UNIT',
            'PRO CODE',
        ];
    }

    private function isMaterialMetaCode(string $code): bool
    {
        return in_array(strtoupper(trim($code)), $this->materialMetaSkipCodes(), true);
    }
}
