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
}
