@extends('layouts.app')

@section('title', 'Database Costing')
@section('page-title', 'Database Costing')

@section('breadcrumb')
    <a href="{{ route('database.parts', absolute: false) }}">Database</a>
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
            min-width: 1980px;
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
        .costing-table th:nth-child(11),
        .costing-table td:nth-child(11),
        .costing-table th:nth-child(12),
        .costing-table td:nth-child(12),
        .costing-table th:nth-child(14),
        .costing-table td:nth-child(14),
        .costing-table th:nth-child(16),
        .costing-table td:nth-child(16) {
            width: 140px;
        }

        .costing-table th:nth-child(13),
        .costing-table td:nth-child(13),
        .costing-table th:nth-child(15),
        .costing-table td:nth-child(15),
        .costing-table th:nth-child(17),
        .costing-table td:nth-child(17) {
            width: 96px;
            text-align: center;
        }

        .costing-table th:nth-child(18),
        .costing-table td:nth-child(18) {
            width: 160px;
        }

        .costing-table th:nth-child(19),
        .costing-table td:nth-child(19) {
            width: 180px;
        }

        .costing-table th:nth-child(20),
        .costing-table td:nth-child(20) {
            width: 160px;
            text-align: center;
        }

        .costing-action-btn {
            width: 38px;
            height: 38px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .costing-action-group {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            align-items: center;
        }

        .costing-delete-btn {
            width: 38px;
            height: 38px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .costing-filter-form {
            display: grid;
            grid-template-columns: repeat(8, minmax(120px, 1fr)) auto;
            gap: 0.5rem;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
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
            justify-content: flex-end;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: nowrap;
        }

        .costing-pagination {
            padding: 0.9rem 1rem 1rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .costing-pagination-summary {
            font-size: 0.82rem;
            color: #64748b;
        }

        .costing-pagination-nav {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }

        .costing-page-link {
            min-width: 34px;
            height: 34px;
            padding: 0 0.6rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            color: #334155;
            text-decoration: none;
            background: #fff;
        }

        .costing-page-link:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .costing-page-link.active {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .costing-page-link.disabled {
            color: #94a3b8;
            border-color: #e2e8f0;
            background: #f8fafc;
            pointer-events: none;
        }

        .costing-filter-btn {
            min-width: 72px;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            line-height: 1.2;
        }

        @media (max-width: 1200px) {
            .costing-filter-form {
                grid-template-columns: repeat(4, minmax(120px, 1fr));
            }

            .costing-filter-actions {
                grid-column: 1 / -1;
                justify-content: flex-start;
            }
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Costing</h3>
        </div>
        <form method="GET" action="{{ route('database.costing', absolute: false) }}" class="costing-filter-form">
            <input type="text" name="period" class="costing-filter-input" value="{{ $filters['period'] ?? '' }}" placeholder="Period">
            <input type="date" name="tanggal" class="costing-filter-input" value="{{ $filters['tanggal'] ?? '' }}">
            <input type="text" name="customer" class="costing-filter-input" value="{{ $filters['customer'] ?? '' }}" placeholder="Customer">
            <input type="text" name="model" class="costing-filter-input" value="{{ $filters['model'] ?? '' }}" placeholder="Model">
            <input type="text" name="id_code" class="costing-filter-input" value="{{ $filters['id_code'] ?? '' }}" placeholder="ID Code">
            <input type="text" name="assy_no" class="costing-filter-input" value="{{ $filters['assy_no'] ?? '' }}" placeholder="Assy No">
            <input type="text" name="assy_name" class="costing-filter-input" value="{{ $filters['assy_name'] ?? '' }}" placeholder="Assy Name">
            <input type="text" name="revisi" class="costing-filter-input" value="{{ $filters['revisi'] ?? '' }}" placeholder="V3">
            <select name="per_page" class="costing-filter-input">
                @foreach([10, 20, 50, 100] as $size)
                    <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }}/halaman</option>
                @endforeach
            </select>
            <div class="costing-filter-actions">
                <button type="submit" class="btn btn-primary btn-sm costing-filter-btn">Search</button>
                <a href="{{ route('database.costing', absolute: false) }}" class="btn btn-secondary btn-sm costing-filter-btn">Reset</a>
            </div>
        </form>
        <div class="costing-table-container">
            <table class="data-table costing-table">
                <thead>
                    <tr>
                        <th>NO.</th>
                        <th>PERIOD</th>
                        <th>DATE</th>
                        <th>CUSTOMER</th>
                        <th>MODEL</th>
                        <th>ID CODE</th>
                        <th>ASSY NO</th>
                        <th>ASSY NAME</th>
                        <th>REVISI</th>
                        <th>QTY/MONTH</th>
                        <th>PRODUCT LIFE</th>
                        <th>MATERIAL COST</th>
                        <th>%</th>
                        <th>PROCESS COST</th>
                        <th>%</th>
                        <th>OVERHEAD COST (TOOLING + ADMIN)</th>
                        <th>%</th>
                        <th>COGM</th>
                        <th>LAST UPDATED</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($costingData as $key => $costing)
                        @php
                            $effectiveOverheadCost = (float) $costing->overhead_cost + (float) $costing->scrap_cost;
                            $cogm = $costing->material_cost + $costing->labor_cost + $effectiveOverheadCost;
                            $materialPct = $cogm > 0 ? ($costing->material_cost / $cogm * 100) : 0;
                            $processPct = $cogm > 0 ? ($costing->labor_cost / $cogm * 100) : 0;
                            $toolingPct = $cogm > 0 ? ($effectiveOverheadCost / $cogm * 100) : 0;
                            $tanggalValue = $costing->trackingRevision?->received_date ?? $costing->created_at;
                            $revisiValue = $costing->trackingRevision?->version_label ?? '-';
                            $formUrl = route('form', array_filter([
                                'id' => $costing->id,
                                'tracking_revision_id' => $costing->tracking_revision_id,
                            ]), absolute: false);
                        @endphp
                        <tr>
                            <td>{{ ($costingData->firstItem() ?? 1) + $key }}</td>
                            <td>{{ $costing->period }}</td>
                            <td>{{ $tanggalValue ? \Carbon\Carbon::parse($tanggalValue)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $costing->customer->name ?? '-' }}</td>
                            <td>{{ $costing->model ?? '-' }}</td>
                            <td>{{ $costing->product->code ?? '-' }}</td>
                            <td>{{ $costing->assy_no ?? '-' }}</td>
                            <td>{{ $costing->assy_name ?? '-' }}</td>
                            <td>{{ $revisiValue }}</td>
                            <td>{{ number_format((float) ($costing->forecast ?? 0), 0, ',', '.') }}</td>
                            <td>{{ number_format((float) ($costing->project_period ?? 0), 0, ',', '.') }} Years</td>
                            <td>Rp {{ number_format($costing->material_cost, 0, ',', '.') }}</td>
                            <td>{{ number_format($materialPct, 2) }}%</td>
                            <td>Rp {{ number_format($costing->labor_cost, 0, ',', '.') }}</td>
                            <td>{{ number_format($processPct, 2) }}%</td>
                            <td>Rp {{ number_format($effectiveOverheadCost, 0, ',', '.') }}</td>
                            <td>{{ number_format($toolingPct, 2) }}%</td>
                            <td><strong>Rp {{ number_format($cogm, 0, ',', '.') }}</strong></td>
                            <td>{{ $costing->updated_at ? \Carbon\Carbon::parse($costing->updated_at)->format('d-m-Y H:i') : '-' }}</td>
                            <td>
                                <div class="costing-action-group">
                                    <a href="{{ $formUrl }}" class="btn btn-secondary btn-sm costing-action-btn" title="Open Form Costing" aria-label="Open Form Costing">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                            <polyline points="14 2 14 8 20 8"/>
                                            <path d="M12 18h-4"/>
                                            <path d="M14.5 12.5l2 2L12 19l-2 0 0-2z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('database.costing.destroy', $costing->id, absolute: false) }}" class="js-confirm-form" data-confirm-message="Hapus baris costing ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm costing-delete-btn" title="Delete Row" aria-label="Delete Row">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                <line x1="10" y1="11" x2="10" y2="17"/>
                                                <line x1="14" y1="11" x2="14" y2="17"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="20" style="text-align: center;">Tidak ada data costing ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="costing-pagination">
            <div class="costing-pagination-summary">
                Menampilkan {{ $costingData->firstItem() ?? 0 }} - {{ $costingData->lastItem() ?? 0 }} dari {{ $costingData->total() }} data
            </div>
            <nav class="costing-pagination-nav" aria-label="Pagination">
                @php
                    $currentPage = $costingData->currentPage();
                    $lastPage = $costingData->lastPage();
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($lastPage, $currentPage + 2);
                @endphp

                @if($costingData->onFirstPage())
                    <span class="costing-page-link disabled">Prev</span>
                @else
                    <a class="costing-page-link" href="{{ $costingData->previousPageUrl() }}">Prev</a>
                @endif

                @for($page = $startPage; $page <= $endPage; $page++)
                    @if($page === $currentPage)
                        <span class="costing-page-link active">{{ $page }}</span>
                    @else
                        <a class="costing-page-link" href="{{ $costingData->url($page) }}">{{ $page }}</a>
                    @endif
                @endfor

                @if($costingData->hasMorePages())
                    <a class="costing-page-link" href="{{ $costingData->nextPageUrl() }}">Next</a>
                @else
                    <span class="costing-page-link disabled">Next</span>
                @endif
            </nav>
        </div>
    </div>
@endsection