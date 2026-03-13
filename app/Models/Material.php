<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
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

    protected $casts = [
        'price' => 'decimal:2',
        'moq' => 'decimal:2',
        'add_cost_import_tax' => 'decimal:2',
        'price_before' => 'decimal:2',
        'price_update' => 'date',
    ];

    public function breakdowns()
    {
        return $this->hasMany(MaterialBreakdown::class);
    }
}
