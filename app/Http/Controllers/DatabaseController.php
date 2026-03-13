<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Material;
use App\Models\CostingData;
use App\Models\CycleTimeTemplate;
use Illuminate\Http\Request;

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

    public function parts()
    {
        $materials = Material::orderBy('material_code', 'asc')->get();
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

    public function destroyCycleTimeTemplate($id)
    {
        $template = CycleTimeTemplate::findOrFail($id);
        $template->delete();

        return redirect(route('database.cycle-time-templates', absolute: false))
            ->with('success', 'Process template berhasil dihapus!');
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

        return redirect(route('database.parts', absolute: false))->with('success', 'Material berhasil ditambahkan!');
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

        return redirect(route('database.parts', absolute: false))->with('success', 'Material berhasil diperbarui!');
    }

    public function destroyPart($id)
    {
        $material = Material::findOrFail($id);
        $material->delete();

        return redirect(route('database.parts', absolute: false))->with('success', 'Material berhasil dihapus!');
    }
}
