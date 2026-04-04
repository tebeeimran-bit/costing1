<?php

namespace Database\Seeders;

use App\Models\CostingData;
use App\Models\Material;
use App\Models\MaterialBreakdown;
use Illuminate\Database\Seeder;

class CostingDetailDummySeeder extends Seeder
{
    private function normalizeUnitLabel(?string $rawUnit, ?string $partName = null, ?string $partNo = null, ?string $proCode = null): string
    {
        $value = strtoupper(trim((string) $rawUnit));
        $textCandidates = implode(' ', array_filter([
            trim((string) $partName),
            trim((string) $partNo),
            trim((string) $proCode),
        ]));
        $textCandidates = strtoupper(trim($textCandidates));

        if ($value === '' || is_numeric($value) || $value === '0') {
            $value = $textCandidates;
        }

        if ($value === '') {
            return 'PCE';
        }

        if (preg_match('/\b(WIRE|CABLE|CORD|HARNESS|TUBE|HOSE|HOSES|METER|MTR|MM)\b/', $value) === 1) {
            return 'Meter';
        }

        if (preg_match('/\b(SET|SETS)\b/', $value) === 1) {
            return 'Set';
        }

        if (preg_match('/\b(UNIT|UNITS)\b/', $value) === 1) {
            return 'Unit';
        }

        if (preg_match('/\b(KG|KGS|KILOGRAM|KILOGRAMS)\b/', $value) === 1) {
            return 'Kg';
        }

        return match (true) {
            in_array($value, ['PCS', 'PCE', 'PC', 'PCS.', 'EA'], true) => 'PCE',
            in_array($value, ['MM', 'MTR', 'METER', 'METRE', 'M'], true) => 'Meter',
            in_array($value, ['SET', 'SETS'], true) => 'Set',
            in_array($value, ['UNIT', 'UNITS'], true) => 'Unit',
            in_array($value, ['KG', 'KGS', 'KILOGRAM', 'KILOGRAMS'], true) => 'Kg',
            default => 'PCE',
        };
    }

    public function run(): void
    {
        $materials = Material::query()
            ->whereNotNull('material_description')
            ->where('material_description', '!=', '')
            ->orderBy('id')
            ->get();

        if ($materials->isEmpty()) {
            $this->command?->warn('No materials found. Skipped CostingDetailDummySeeder.');
            return;
        }

        $costingList = CostingData::query()
            ->with(['materialBreakdowns'])
            ->orderBy('id')
            ->get();

        foreach ($costingList as $costing) {
            $existingBreakdowns = $costing->materialBreakdowns;
            $needsMaterialBreakdowns = $existingBreakdowns->isEmpty();
            $cycleTimes = is_array($costing->cycle_times) ? $costing->cycle_times : [];
            $needsCycleTimes = empty($cycleTimes);

            if ($needsMaterialBreakdowns) {
                $selectedMaterials = $materials->count() >= 4
                    ? $materials->shuffle()->take(4)->values()
                    : $materials->shuffle()->values();

                $weights = [0.38, 0.27, 0.21, 0.14];
                $materialCostTotal = (float) ($costing->material_cost ?? 0);
                if ($materialCostTotal <= 0) {
                    $materialCostTotal = 1000;
                }

                foreach ($selectedMaterials as $index => $material) {
                    $weight = $weights[$index] ?? (1 / max(1, $selectedMaterials->count()));
                    $lineCost = $materialCostTotal * $weight;

                    $basePrice = (float) ($material->price ?? 0);
                    $unitPriceBasis = $basePrice > 0 ? $basePrice : max(1, round($lineCost / 100));
                    $qtyReq = $unitPriceBasis > 0 ? ($lineCost / $unitPriceBasis) : 0;

                    $currency = trim((string) ($material->currency ?? ''));
                    if ($currency === '') {
                        $currency = 'IDR';
                    }

                    $taxPercent = (float) ($material->add_cost_import_tax ?? 0);
                    if ($taxPercent < 0) {
                        $taxPercent = 0;
                    }

                    $amount2 = $lineCost * (1 + ($taxPercent / 100));
                    $unitPrice2 = $qtyReq > 0 ? ($amount2 / $qtyReq) : $unitPriceBasis;

                    MaterialBreakdown::create([
                        'costing_data_id' => $costing->id,
                        'material_id' => $material->id,
                        'row_no' => (string) ($index + 1),
                        'part_no' => (string) ($material->material_code ?? '-'),
                        'id_code' => (string) ($material->material_code ?? '-'),
                        'part_name' => (string) ($material->material_description ?? '-'),
                        'pro_code' => (string) ($costing->product->code ?? '-'),
                        'qty_req' => round($qtyReq, 4),
                        'amount1' => round($lineCost, 4),
                        'unit_price_basis' => round($unitPriceBasis, 4),
                        'unit_price_basis_text' => $this->normalizeUnitLabel($material->base_uom, $material->material_description, $material->material_code, (string) ($costing->product->code ?? '')),
                        'currency' => $currency,
                        'qty_moq' => round($qtyReq, 4),
                        'cn_type' => 'N',
                        'import_tax_percent' => round($taxPercent, 2),
                        'amount2' => round($amount2, 4),
                        'currency2' => $currency,
                        'unit_price2' => round($unitPrice2, 4),
                    ]);
                }
            }

            $breakdownsToNormalize = $costing->materialBreakdowns()->with('material')->orderBy('id')->get();
            if ($breakdownsToNormalize->isNotEmpty()) {
                $targetMaterialCost = (float) ($costing->material_cost ?? 0);
                $currentMaterialCost = (float) $breakdownsToNormalize->sum('amount1');
                $scaleFactor = $currentMaterialCost > 0 ? ($targetMaterialCost / $currentMaterialCost) : 0;

                if ($currentMaterialCost <= 0 && $targetMaterialCost > 0) {
                    $equalShare = $targetMaterialCost / max(1, $breakdownsToNormalize->count());
                } else {
                    $equalShare = null;
                }

                foreach ($breakdownsToNormalize as $index => $breakdown) {
                    $material = $breakdown->material;
                    $unitLabel = $this->normalizeUnitLabel(
                        $material?->base_uom,
                        $breakdown->part_name,
                        $breakdown->part_no,
                        $breakdown->pro_code
                    );

                    $newAmount1 = $equalShare !== null
                        ? $equalShare
                        : ((float) $breakdown->amount1 * $scaleFactor);

                    $newAmount2 = $equalShare !== null
                        ? $equalShare
                        : ((float) $breakdown->amount2 * $scaleFactor);

                    $qtyReq = (float) ($breakdown->qty_req ?? 0);
                    $unitPrice2 = $qtyReq > 0 ? ($newAmount2 / $qtyReq) : $newAmount2;

                    $breakdown->update([
                        'row_no' => (string) ($breakdown->row_no ?? ($index + 1)),
                        'unit_price_basis_text' => $unitLabel,
                        'amount1' => round($newAmount1, 4),
                        'amount2' => round($newAmount2, 4),
                        'unit_price2' => round($unitPrice2, 4),
                    ]);
                }
            }

            if ($needsCycleTimes) {
                $forecastQty = (float) ($costing->forecast ?? 0);
                $cuttingQty = (int) max(1, round($forecastQty / 100));

                $cycleTimesDummy = [
                    [
                        'process' => 'Cutting, Stripping',
                        'qty' => $cuttingQty,
                        'time_hour' => 0.85,
                        'time_sec' => 3060,
                        'time_sec_per_qty' => $cuttingQty > 0 ? round(3060 / $cuttingQty, 4) : 0,
                        'cost_per_sec' => 12.5,
                        'cost_per_unit' => 1450,
                        'area_of_process' => 'PP - Preparation',
                    ],
                    [
                        'process' => 'Terminal Crimping',
                        'qty' => $cuttingQty,
                        'time_hour' => 0.72,
                        'time_sec' => 2592,
                        'time_sec_per_qty' => $cuttingQty > 0 ? round(2592 / $cuttingQty, 4) : 0,
                        'cost_per_sec' => 13.2,
                        'cost_per_unit' => 1320,
                        'area_of_process' => 'PP - Preparation',
                    ],
                    [
                        'process' => 'Sub Assy',
                        'qty' => $cuttingQty,
                        'time_hour' => 0.66,
                        'time_sec' => 2376,
                        'time_sec_per_qty' => $cuttingQty > 0 ? round(2376 / $cuttingQty, 4) : 0,
                        'cost_per_sec' => 14.1,
                        'cost_per_unit' => 1190,
                        'area_of_process' => 'FA - Final Assy',
                    ],
                    [
                        'process' => 'Final Inspection',
                        'qty' => $cuttingQty,
                        'time_hour' => 0.41,
                        'time_sec' => 1476,
                        'time_sec_per_qty' => $cuttingQty > 0 ? round(1476 / $cuttingQty, 4) : 0,
                        'cost_per_sec' => 11.8,
                        'cost_per_unit' => 910,
                        'area_of_process' => 'FA - Final Assy',
                    ],
                ];

                $costing->update([
                    'cycle_times' => $cycleTimesDummy,
                ]);
            }
        }

        $this->command?->info('CostingDetailDummySeeder finished.');
    }
}
