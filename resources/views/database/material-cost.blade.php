@extends('layouts.app')

@section('title', 'Database Material Cost')
@section('page-title', 'Database Material Cost')

@section('breadcrumb')
    <a href="{{ route('database.parts', absolute: false) }}">Database</a>
    <span class="breadcrumb-separator">/</span>
    <span>Material Cost</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <h3 class="card-title">Material Cost per Assy No -> Model -> Customer -> Business Category</h3>
            <form method="GET" action="{{ route('database.material-cost', absolute: false) }}" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                <label for="period" style="font-size: 0.85rem; color: var(--slate-600);">Periode</label>
                <select id="period" name="period" onchange="this.form.submit()" style="border: 1px solid var(--slate-300); border-radius: 8px; padding: 0.4rem 0.55rem; font-size: 0.85rem; background: #fff; color: var(--slate-700);">
                    <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Semua</option>
                    @foreach($periodOptions as $p)
                        <option value="{{ $p }}" {{ $period === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div style="padding: 0 1rem 1rem 1rem; color: var(--slate-600); font-size: 0.85rem;">
            <span style="font-weight: 700; color: var(--slate-800);">Total Material Cost:</span>
            Rp {{ number_format($totalMaterialCost, 0, ',', '.') }}
            <span style="margin-left: 1rem; font-weight: 700; color: var(--slate-800);">Total Project:</span>
            {{ number_format($totalProjects, 0, ',', '.') }}
        </div>

        <div class="material-table-container">
            <table class="data-table" style="min-width: 1100px; width: 100%;">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Assy No</th>
                        <th>Model</th>
                        <th>Customer</th>
                        <th>Business Category</th>
                        <th class="text-right">Material Cost</th>
                        <th class="text-right">Projects</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materialCostRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->assy_no }}</td>
                            <td>{{ $row->model }}</td>
                            <td>{{ $row->customer_name }}</td>
                            <td>{{ $row->business_category }}</td>
                            <td class="text-right">Rp {{ number_format((float) ($row->material_cost_total ?? 0), 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format((int) ($row->project_count ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="color: var(--slate-400);">Belum ada data material cost.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
