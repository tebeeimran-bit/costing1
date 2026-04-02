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
        .costing-table-viewport {
            width: 100%;
            overflow: hidden;
        }

        .costing-table-container {
            overflow: hidden;
        }

        .costing-table {
            table-layout: fixed;
            width: calc(100% / 0.78);
            transform: scale(0.78);
            transform-origin: top left;
        }

        .costing-table th,
        .costing-table td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
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

        .costing-table th:nth-child(19),
        .costing-table td:nth-child(19) {
            width: 130px;
            text-align: center;
        }

        .costing-action-btn {
            min-width: 108px;
            white-space: nowrap;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Costing</h3>
        </div>
        <div class="costing-table-container">
            <div class="costing-table-viewport">
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
            </div>
        </div>
    </div>
@endsection