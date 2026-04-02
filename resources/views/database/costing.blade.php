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
                        <th>TANGGAL</th>
                        <th>CUSTOMER</th>
                        <th>MODEL</th>
                        <th>ID CODE</th>
                        <th>ASSY NO</th>
                        <th>PRODUCT</th>
                        <th>REVISI</th>
                        <th>MATERIAL COST</th>
                        <th>%</th>
                        <th>PROCESS COST</th>
                        <th>%</th>
                        <th>DEPRESIASI TOOLING COST</th>
                        <th>%</th>
                        <th>COGM</th>
                        <th>LAST UPDATED</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($costingData as $key => $costing)
                        @php
                            $cogm = $costing->material_cost + $costing->labor_cost + $costing->overhead_cost;
                            $materialPct = $cogm > 0 ? ($costing->material_cost / $cogm * 100) : 0;
                            $processPct = $cogm > 0 ? ($costing->labor_cost / $cogm * 100) : 0;
                            $toolingPct = $cogm > 0 ? ($costing->overhead_cost / $cogm * 100) : 0;
                            $tanggalValue = $costing->trackingRevision?->received_date ?? $costing->created_at;
                            $revisiValue = $costing->trackingRevision?->version_label ?? '-';
                            $formUrl = route('form', array_filter([
                                'id' => $costing->id,
                                'tracking_revision_id' => $costing->tracking_revision_id,
                            ]), absolute: false);
                        @endphp
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $costing->period }}</td>
                            <td>{{ $tanggalValue ? \Carbon\Carbon::parse($tanggalValue)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $costing->customer->name ?? '-' }}</td>
                            <td>{{ $costing->model ?? '-' }}</td>
                            <td>{{ $costing->product->code ?? '-' }}</td>
                            <td>{{ $costing->assy_no ?? '-' }}</td>
                            <td>{{ $costing->product->name ?? '-' }}</td>
                            <td>{{ $revisiValue }}</td>
                            <td>Rp {{ number_format($costing->material_cost, 0, ',', '.') }}</td>
                            <td>{{ number_format($materialPct, 2) }}%</td>
                            <td>Rp {{ number_format($costing->labor_cost, 0, ',', '.') }}</td>
                            <td>{{ number_format($processPct, 2) }}%</td>
                            <td>Rp {{ number_format($costing->overhead_cost, 0, ',', '.') }}</td>
                            <td>{{ number_format($toolingPct, 2) }}%</td>
                            <td><strong>Rp {{ number_format($cogm, 0, ',', '.') }}</strong></td>
                            <td>{{ $costing->updated_at ? \Carbon\Carbon::parse($costing->updated_at)->format('d-m-Y H:i') : '-' }}</td>
                            <td>
                                <a href="{{ $formUrl }}" class="btn btn-secondary btn-sm" style="white-space: nowrap;">
                                    TO FORM INPUT
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="18" style="text-align: center;">Tidak ada data costing ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection