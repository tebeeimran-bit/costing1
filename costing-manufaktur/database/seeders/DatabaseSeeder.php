<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Material;
use App\Models\CostingData;
use App\Models\MaterialBreakdown;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CycleTimeTemplateSeeder::class);

        // Seed Products
        $products = [
            ['code' => 'PCB-B', 'name' => 'PCB B', 'line' => 'Line A'],
            ['code' => 'MTR-X', 'name' => 'Motor X', 'line' => 'Line A'],
            ['code' => 'KBL-A', 'name' => 'Kabel A', 'line' => 'Line B'],
            ['code' => 'RLY-Y', 'name' => 'Relay Y', 'line' => 'Line B'],
            ['code' => 'SWT-Z', 'name' => 'Switch Z', 'line' => 'Line C'],
        ];
        
        foreach ($products as $product) {
            Product::create($product);
        }

        // Seed Customers
        $customers = [
            ['code' => 'PLN', 'name' => 'PLN'],
            ['code' => 'TLKM', 'name' => 'Telkom'],
            ['code' => 'ASTR', 'name' => 'Astra'],
            ['code' => 'SMSG', 'name' => 'Samsung'],
            ['code' => 'YMH', 'name' => 'Yamaha'],
        ];
        
        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        // Seed Materials
        $materials = [
            ['part_no' => 'MAT-001', 'id_code' => 'CU-001', 'part_name' => 'Copper Wire', 'unit' => 'KG', 'pro_code' => 'PRO-A', 'supplier_name' => 'PT Tembaga Jaya'],
            ['part_no' => 'MAT-002', 'id_code' => 'ST-001', 'part_name' => 'Steel Plate', 'unit' => 'KG', 'pro_code' => 'PRO-A', 'supplier_name' => 'PT Baja Utama'],
            ['part_no' => 'MAT-003', 'id_code' => 'IC-001', 'part_name' => 'IC Chips', 'unit' => 'PCS', 'pro_code' => 'PRO-B', 'supplier_name' => 'Samsung Elec'],
            ['part_no' => 'MAT-004', 'id_code' => 'EV-001', 'part_name' => 'EVC Cable', 'unit' => 'MM', 'pro_code' => 'PRO-C', 'supplier_name' => 'PT Kabel Indo'],
            ['part_no' => 'MAT-005', 'id_code' => 'PL-001', 'part_name' => 'Plastic Case', 'unit' => 'PCS', 'pro_code' => 'PRO-D', 'supplier_name' => 'PT Plastik Maju'],
            ['part_no' => 'MAT-006', 'id_code' => 'RS-001', 'part_name' => 'Resistor 10K', 'unit' => 'PCS', 'pro_code' => 'PRO-E', 'supplier_name' => 'Yageo Corp'],
            ['part_no' => 'MAT-007', 'id_code' => 'CP-001', 'part_name' => 'Capacitor 100uF', 'unit' => 'PCS', 'pro_code' => 'PRO-E', 'supplier_name' => 'Murata Mfg'],
            ['part_no' => 'MAT-008', 'id_code' => 'SP-001', 'part_name' => 'Spring Steel', 'unit' => 'KG', 'pro_code' => 'PRO-A', 'supplier_name' => 'PT Spring Indo'],
        ];
        
        foreach ($materials as $material) {
            Material::create($material);
        }

        // Seed Costing Data
        $costingDataList = [
            [
                'product_id' => 1, // PCB B
                'customer_id' => 1, // PLN
                'period' => '2025-01',
                'wo_number' => 'WO-2025-001',
                'exchange_rate_usd' => 15500,
                'exchange_rate_jpy' => 103,
                'lme_rate' => 8500,
                'forecast' => 5000,
                'project_period' => 12,
                'material_cost' => 15600000000,
                'labor_cost' => 6240000000,
                'overhead_cost' => 6240000000,
                'scrap_cost' => 520000000,
                'revenue' => 32000000000,
                'qty_good' => 55000,
            ],
            [
                'product_id' => 2, // Motor X
                'customer_id' => 2, // Telkom
                'period' => '2025-01',
                'wo_number' => 'WO-2025-002',
                'exchange_rate_usd' => 15500,
                'exchange_rate_jpy' => 103,
                'lme_rate' => 8500,
                'forecast' => 8000,
                'project_period' => 12,
                'material_cost' => 17100000000,
                'labor_cost' => 5700000000,
                'overhead_cost' => 5415000000,
                'scrap_cost' => 285000000,
                'revenue' => 35000000000,
                'qty_good' => 80000,
            ],
            [
                'product_id' => 3, // Kabel A
                'customer_id' => 3, // Astra
                'period' => '2025-01',
                'wo_number' => 'WO-2025-003',
                'exchange_rate_usd' => 15500,
                'exchange_rate_jpy' => 103,
                'lme_rate' => 8500,
                'forecast' => 10000,
                'project_period' => 12,
                'material_cost' => 10800000000,
                'labor_cost' => 3600000000,
                'overhead_cost' => 3420000000,
                'scrap_cost' => 180000000,
                'revenue' => 22000000000,
                'qty_good' => 100000,
            ],
            [
                'product_id' => 4, // Relay Y
                'customer_id' => 4, // Samsung
                'period' => '2025-01',
                'wo_number' => 'WO-2025-004',
                'exchange_rate_usd' => 15500,
                'exchange_rate_jpy' => 103,
                'lme_rate' => 8500,
                'forecast' => 6000,
                'project_period' => 12,
                'material_cost' => 5250000000,
                'labor_cost' => 1750000000,
                'overhead_cost' => 1662500000,
                'scrap_cost' => 87500000,
                'revenue' => 11000000000,
                'qty_good' => 50000,
            ],
            [
                'product_id' => 5, // Switch Z
                'customer_id' => 5, // Yamaha
                'period' => '2025-01',
                'wo_number' => 'WO-2025-005',
                'exchange_rate_usd' => 15500,
                'exchange_rate_jpy' => 103,
                'lme_rate' => 8500,
                'forecast' => 3000,
                'project_period' => 12,
                'material_cost' => 2100000000,
                'labor_cost' => 700000000,
                'overhead_cost' => 665000000,
                'scrap_cost' => 35000000,
                'revenue' => 4500000000,
                'qty_good' => 35000,
            ],
        ];
        
        foreach ($costingDataList as $costingData) {
            $cd = CostingData::create($costingData);
            
            // Add material breakdowns for each costing data
            $breakdowns = [
                [
                    'costing_data_id' => $cd->id,
                    'material_id' => 1,
                    'qty_req' => 2.5,
                    'amount1' => 100,
                    'unit_price_basis' => 85000,
                    'currency' => 'IDR',
                    'qty_moq' => 1000,
                    'cn_type' => 'N',
                    'import_tax_percent' => 5,
                    'amount2' => 100,
                    'currency2' => 'IDR',
                    'unit_price2' => 89250,
                ],
                [
                    'costing_data_id' => $cd->id,
                    'material_id' => 2,
                    'qty_req' => 1.8,
                    'amount1' => 50,
                    'unit_price_basis' => 45000,
                    'currency' => 'IDR',
                    'qty_moq' => 500,
                    'cn_type' => 'N',
                    'import_tax_percent' => 2.5,
                    'amount2' => 50,
                    'currency2' => 'IDR',
                    'unit_price2' => 46125,
                ],
                [
                    'costing_data_id' => $cd->id,
                    'material_id' => 3,
                    'qty_req' => 5,
                    'amount1' => 200,
                    'unit_price_basis' => 2.5,
                    'currency' => 'USD',
                    'qty_moq' => 10000,
                    'cn_type' => 'C',
                    'import_tax_percent' => 7.5,
                    'amount2' => 200,
                    'currency2' => 'USD',
                    'unit_price2' => 2.69,
                ],
            ];
            
            foreach ($breakdowns as $breakdown) {
                MaterialBreakdown::create($breakdown);
            }
        }
        
        // Add more periods for trend data
        $additionalPeriods = ['2024-07', '2024-08', '2024-09', '2024-10', '2024-11', '2024-12'];
        $baseCosts = [18000000000, 17500000000, 17200000000, 17000000000, 16800000000, 16500000000];
        
        foreach ($additionalPeriods as $index => $period) {
            CostingData::create([
                'product_id' => 1,
                'customer_id' => 1,
                'period' => $period,
                'wo_number' => 'WO-' . str_replace('-', '', $period) . '-001',
                'exchange_rate_usd' => 15500,
                'exchange_rate_jpy' => 103,
                'lme_rate' => 8500,
                'forecast' => 5000,
                'project_period' => 12,
                'material_cost' => $baseCosts[$index] * 0.6,
                'labor_cost' => $baseCosts[$index] * 0.2,
                'overhead_cost' => $baseCosts[$index] * 0.18,
                'scrap_cost' => $baseCosts[$index] * 0.02,
                'revenue' => $baseCosts[$index] * 1.15,
                'qty_good' => 50000 + ($index * 2000),
            ]);
        }
    }
}
