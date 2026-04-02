@extends('layouts.app')

@section('title', 'Database Costing')
@section('page-title', 'Database Costing')

@section('breadcrumb')
    <a href="{{ route('database.products', absolute: false) }}">Database</a>
    <span class="breadcrumb-separator">/</span>
    <span>Costing</span>
@endsection

@section('content')
    <style>
        .costing-table-container {
            overflow-x: auto;
        }

        .costing-table {
            table-layout: fixed;
            min-width: 1780px;
        }

        .costing-table th,
        .costing-table td {
            vertical-align: middle;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            text-overflow: clip;
        }

        .costing-table th:nth-child(1),
        .costing-table td:nth-child(1) {
            width: 52px;
            text-align: center;
        }

        .costing-table th:nth-child(2),
        .costing-table td:nth-child(2) {
            width: 100px;
        }

        .costing-table th:nth-child(3),
        .costing-table td:nth-child(3) {
            width: 130px;
        }

        .costing-table th:nth-child(4),
        .costing-table td:nth-child(4) {
            width: 200px;
        }

        .costing-table th:nth-child(5),
        .costing-table td:nth-child(5) {
            width: 96px;
        }

        .costing-table th:nth-child(6),
        .costing-table td:nth-child(6) {
            width: 120px;
        }

        .costing-table th:nth-child(7),
        .costing-table td:nth-child(7) {
            width: 240px;
        }

        .costing-table th:nth-child(8),
        .costing-table td:nth-child(8) {
            width: 170px;
        }

        .costing-table th:nth-child(9),
        .costing-table td:nth-child(9) {
            width: 96px;
            text-align: center;
        }

        .costing-table th:nth-child(10),
        .costing-table td:nth-child(10),
        .costing-table th:nth-child(12),
        .costing-table td:nth-child(12),
        .costing-table th:nth-child(14),
        .costing-table td:nth-child(14),
        .costing-table th:nth-child(16),
        .costing-table td:nth-child(16) {
            width: 140px;
        }

        .costing-table th:nth-child(11),
        .costing-table td:nth-child(11),
        .costing-table th:nth-child(13),
        .costing-table td:nth-child(13),
        .costing-table th:nth-child(15),
        .costing-table td:nth-child(15) {
            width: 96px;
            text-align: center;
        }

        .costing-table th:nth-child(17),
        .costing-table td:nth-child(17) {
            width: 160px;
        }

        .costing-table th:nth-child(18),
        .costing-table td:nth-child(18) {
            width: 180px;
            text-align: center;
        }

        .costing-action-btn {
            min-width: 108px;
            white-space: nowrap;
        }

        .costing-filter-row th {
            background: #eef2ff;
            border-bottom: 1px solid #cbd5e1;
            padding: 0.45rem 0.5rem;
        }

        .costing-filter-input {
            width: 100%;
            min-height: 30px;
            padding: 0.35rem 0.45rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.35rem;
            font-size: 0.75rem;
            color: #1e293b;
            background: #fff;
        }

        .costing-filter-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }

        .costing-filter-btn {
            min-width: 64px;
            padding: 0.3rem 0.55rem;
            font-size: 0.7rem;
            line-height: 1.2;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Costing</h3>
        </div>
        <div class="costing-table-container">
            <form method="GET" action="{{ route('database.costing', absolute: false) }}">
            <table class="data-table costing-table">
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
                    <tr class="costing-filter-row">
                        <th></th>
                        <th><input type="text" name="period" class="costing-filter-input" value="{{ $filters['period'] ?? '' }}" placeholder="Periode"></th>
                        <th><input type="date" name="tanggal" class="costing-filter-input" value="{{ $filters['tanggal'] ?? '' }}"></th>
                        <th><input type="text" name="customer" class="costing-filter-input" value="{{ $filters['customer'] ?? '' }}" placeholder="Customer"></th>
                        <th><input type="text" name="model" class="costing-filter-input" value="{{ $filters['model'] ?? '' }}" placeholder="Model"></th>
                        <th><input type="text" name="id_code" class="costing-filter-input" value="{{ $filters['id_code'] ?? '' }}" placeholder="ID Code"></th>
                        <th><input type="text" name="assy_no" class="costing-filter-input" value="{{ $filters['assy_no'] ?? '' }}" placeholder="Assy No"></th>
                        <th><input type="text" name="product" class="costing-filter-input" value="{{ $filters['product'] ?? '' }}" placeholder="Product"></th>
                        <th><input type="text" name="revisi" class="costing-filter-input" value="{{ $filters['revisi'] ?? '' }}" placeholder="V3"></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th>
                            <div class="costing-filter-actions">
                                <button type="submit" class="btn btn-primary btn-sm costing-filter-btn">Search</button>
                                <a href="{{ route('database.costing', absolute: false) }}" class="btn btn-secondary btn-sm costing-filter-btn">Reset</a>
                            </div>
                        </th>
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
                                <a href="{{ $formUrl }}" class="btn btn-secondary btn-sm costing-action-btn">
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
            </form>
        </div>
    </div>
@endsection