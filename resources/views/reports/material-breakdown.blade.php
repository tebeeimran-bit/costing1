@extends('layouts.app')
@section('title', 'Material Breakdown')
@section('page-title', 'Material Breakdown')
@section('breadcrumb')
    <a href="{{ route('database.parts') }}">Database</a>
    <span class="breadcrumb-separator">/</span>
    <span>Material Breakdown</span>
@endsection

@section('content')
<style>
    .mb-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .mb-card { padding: 1.25rem; border-radius: 12px; color: #fff; }
    .mb-card .mc-label { font-size: 0.78rem; font-weight: 600; opacity: 0.9; }
    .mb-card .mc-value { font-size: 1.5rem; font-weight: 800; }
    .mb-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
    .mb-table th { background: var(--blue-600); color: #fff; padding: 0.55rem 0.7rem; text-align: left; font-size: 0.75rem; }
    .mb-table td { padding: 0.45rem 0.7rem; border-bottom: 1px solid var(--slate-200); }
    .mb-table tr:hover { background: #f8fafc; }
</style>

<div class="mb-cards">
    <div class="mb-card" style="background: linear-gradient(135deg, #f97316, #ea580c);">
        <div class="mc-label">Total Material Parts</div>
        <div class="mc-value">{{ number_format($totalParts, 0, ',', '.') }}</div>
    </div>
    <div class="mb-card" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
        <div class="mc-label">Unique Part Numbers</div>
        <div class="mc-value">{{ number_format($uniqueParts, 0, ',', '.') }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 0.75rem;">
        <h3 class="card-title">Daftar Material Breakdown</h3>
        <form method="GET" action="{{ route('material-breakdown') }}" style="display:flex; gap:0.5rem; align-items:center;">
            <input type="text" name="search" value="{{ $search }}" class="form-input" placeholder="Cari part number / name..." style="padding:0.45rem 0.75rem; font-size:0.85rem; width:260px;">
            <button type="submit" class="btn btn-primary" style="padding:0.45rem 0.75rem; font-size:0.85rem;">Cari</button>
            @if($search)<a href="{{ route('material-breakdown') }}" class="btn btn-secondary" style="padding:0.45rem 0.75rem; font-size:0.85rem;">Reset</a>@endif
        </form>
    </div>
    <div class="material-table-container">
        <table class="mb-table" style="min-width: 1000px;">
            <thead>
                <tr><th>No.</th><th>Part No</th><th>ID Code</th><th>Part Name</th><th>Customer</th><th>Model</th><th style="text-align:right;">Qty Req</th><th style="text-align:right;">Unit Price</th><th>Currency</th><th style="text-align:right;">Amount</th></tr>
            </thead>
            <tbody>
                @forelse($breakdowns as $i => $b)
                <tr>
                    <td>{{ $breakdowns->firstItem() + $i }}</td>
                    <td><strong>{{ $b->part_no ?? '-' }}</strong></td>
                    <td>{{ $b->id_code ?? '-' }}</td>
                    <td>{{ $b->part_name ?? '-' }}</td>
                    <td>{{ $b->costingData?->customer?->name ?? '-' }}</td>
                    <td>{{ $b->costingData?->model ?? '-' }}</td>
                    <td style="text-align:right;">{{ $b->qty_req ?? '-' }}</td>
                    <td style="text-align:right;">{{ $b->unit_price_basis ? number_format((float)$b->unit_price_basis, 4, ',', '.') : '-' }}</td>
                    <td>{{ $b->currency ?? '-' }}</td>
                    <td style="text-align:right; font-weight:600;">{{ $b->amount1 ? number_format((float)$b->amount1, 2, ',', '.') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="10" style="text-align:center; color:var(--slate-400); padding:2rem;">Tidak ada data material breakdown.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($breakdowns->hasPages())
    <div style="padding: 1rem; border-top: 1px solid var(--slate-200); display:flex; justify-content:space-between; align-items:center;">
        <span style="font-size:0.82rem; color:var(--slate-500);">Halaman {{ $breakdowns->currentPage() }} dari {{ $breakdowns->lastPage() }} · {{ $breakdowns->total() }} data</span>
        <div class="doc-pagination">{{ $breakdowns->links('pagination.doc-paginator') }}</div>
    </div>
    @endif
</div>
@endsection
