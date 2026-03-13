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
                        <th>ID</th>
                        <th>Periode</th>
                        <th>Model</th>
                        <th>Assy No</th>
                        <th>Produk</th>
                        <th>Customer</th>
                        <th>Material Cost</th>
                        <th>Labor Cost</th>
                        <th>Overhead Cost</th>
                        <th>Total Cost</th>
                        <th>Margin (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($costingData as $costing)
                        <tr>
                            <td>{{ $costing->id }}</td>
                            <td>{{ $costing->period }}</td>
                            <td>{{ $costing->model ?? '-' }}</td>
                            <td>{{ $costing->assy_no ?? '-' }}</td>
                            <td>{{ $costing->product->name ?? '-' }}</td>
                            <td>{{ $costing->customer->name ?? '-' }}</td>
                            <td>Rp {{ number_format($costing->material_cost, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($costing->labor_cost, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($costing->overhead_cost, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($costing->total_cost, 0, ',', '.') }}</td>
                            <td>{{ number_format($costing->margin, 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align: center;">Tidak ada data costing ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection