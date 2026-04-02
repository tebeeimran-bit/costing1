@extends('layouts.app')

@section('title', 'Database Costing')
@section('page-title', 'Database Costing')

@section('breadcrumb')
    <a href="{{ route('database.products', absolute: false) }}">Database</a>
    <span class="breadcrumb-separator">/</span>
    <span>Costing</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Costing</h3>
        </div>
        <div class="material-table-container" style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>NO.</th>
                        <th>PERIODE</th>
                        <th>CUSTOMER</th>
                        <th>MODEL</th>
                        <th>ID CODE</th>
                        <th>ASSY NO</th>
                        <th>PRODUCT</th>
                        <th>MATERIAL COST</th>
                        <th>%</th>
                        <th>PROCESS COST</th>
                        <th>%</th>
                        <th>TOOLING COST</th>
                        <th>%</th>
                        <th>COGM</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($costingData as $key => $costing)
                        @php
                            $cogm = $costing->material_cost + $costing->labor_cost + $costing->overhead_cost;
                            $materialPct = $cogm > 0 ? ($costing->material_cost / $cogm * 100) : 0;
                            $processPct = $cogm > 0 ? ($costing->labor_cost / $cogm * 100) : 0;
                            $toolingPct = $cogm > 0 ? ($costing->overhead_cost / $cogm * 100) : 0;
                        @endphp
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $costing->period }}</td>
                            <td>{{ $costing->customer->name ?? '-' }}</td>
                            <td>{{ $costing->model ?? '-' }}</td>
                            <td>{{ $costing->id }}</td>
                            <td>{{ $costing->assy_no ?? '-' }}</td>
                            <td>{{ $costing->product->name ?? '-' }}</td>
                            <td>Rp {{ number_format($costing->material_cost, 0, ',', '.') }}</td>
                            <td>{{ number_format($materialPct, 2) }}%</td>
                            <td>Rp {{ number_format($costing->labor_cost, 0, ',', '.') }}</td>
                            <td>{{ number_format($processPct, 2) }}%</td>
                            <td>Rp {{ number_format($costing->overhead_cost, 0, ',', '.') }}</td>
                            <td>{{ number_format($toolingPct, 2) }}%</td>
                            <td><strong>Rp {{ number_format($cogm, 0, ',', '.') }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" style="text-align: center;">Tidak ada data costing ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection