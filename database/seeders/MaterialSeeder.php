<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks to allow truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Material::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $plants = ['1501', '1502', '1503'];
        $materialTypes = ['ZRAW', 'ZMNT', 'ZCUS', 'ZOHP', 'ZCON', 'ZPCK', 'ZSEM', 'ZFIN', 'ZSAM', 'ZGSS'];
        $materialGroups = ['A16', 'B22', 'C08', 'D45', 'E12', 'F33', 'G07', 'H29', 'I18', 'J55'];
        $uomList = ['MM', 'MTR', 'PCS', 'KG', 'SET', 'ROL', 'LTR', 'BOX'];
        $purchaseUnits = ['MTR', 'KG', 'PCS', 'SET', 'ROL', 'BOX', 'LTR', 'PAC'];
        $currencies = ['IDR', 'USD', 'JPY'];
        $makers = ['Yazaki', 'Sumitomo', 'TE Connectivity', 'Molex', 'Amphenol', 'Tyco', 'JST', 'Hirose', 'Furukawa', 'AVSS'];

        $descriptions = [
            'Wire AV 0.5 B',
            'Terminal Connector 2.3MM',
            'Housing 6 Pin Black',
            'Grommet Rubber Round',
            'Tube PVC 5MM Clear',
            'Clip Wire Holder 10MM',
            'Tape Vinyl 19MM Black',
            'Corrugated Tube 7MM',
            'Seal Connector Waterproof',
            'Cover Terminal Protection',
            'Wire AVSS 1.25 R',
            'Joint Connector Y Type',
            'Protector Wire Bundle',
            'Band Cable Tie 200MM',
            'Sleeve Heat Shrink 6MM',
            'Wire AVS 2.0 W',
            'Terminal Ring M6',
            'Fuse Holder Inline',
            'Relay Socket 5 Pin',
            'Switch Toggle SPDT',
        ];

        $materials = [];

        for ($i = 1; $i <= 20; $i++) {
            $price = rand(100, 50000);
            $priceUpdate = Carbon::now()->subDays(rand(1, 365));
            $priceBefore = $price - rand(10, 500);
            if ($priceBefore < 0)
                $priceBefore = 0;

            $materials[] = [
                'plant' => $plants[array_rand($plants)],
                'material_code' => sprintf('%04d-%03d%s%s', rand(1000, 9999), rand(1, 999), chr(rand(65, 90)), chr(rand(65, 90))),
                'material_description' => $descriptions[$i - 1],
                'material_type' => $materialTypes[array_rand($materialTypes)],
                'material_group' => $materialGroups[array_rand($materialGroups)],
                'base_uom' => $uomList[array_rand($uomList)],
                'price' => $price,
                'purchase_unit' => $purchaseUnits[array_rand($purchaseUnits)],
                'currency' => $currencies[array_rand($currencies)],
                'moq' => rand(1, 1000) * 10,
                'cn' => rand(0, 1) ? 'C' : 'N',
                'maker' => $makers[array_rand($makers)],
                'add_cost_import_tax' => rand(0, 1) ? rand(1, 15) : null,
                'price_update' => $priceUpdate,
                'price_before' => $priceBefore,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Material::insert($materials);
    }
}
