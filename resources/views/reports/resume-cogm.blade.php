@extends('layouts.app')
@section('title', 'Resume COGM')
@section('page-title', 'Resume COGM')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Menu Utama</a>
    <span class="breadcrumb-separator">/</span>
    <span>Resume COGM</span>
@endsection

@section('content')
<style>
    .resume-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .resume-card { padding: 1.25rem; border-radius: 12px; color: #fff; }
    .resume-card .rc-label { font-size: 0.78rem; font-weight: 600; opacity: 0.9; }
    .resume-card .rc-value { font-size: 1.5rem; font-weight: 800; }
    .resume-card .rc-sub { font-size: 0.75rem; opacity: 0.8; }
    .cust-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .cust-table th { background: var(--blue-600); color: #fff; padding: 0.6rem 0.75rem; text-align: left; }
    .cust-table td { padding: 0.55rem 0.75rem; border-bottom: 1px solid var(--slate-200); }
    .cust-table tr:hover { background: #f8fafc; }
    .status-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 0.3rem; }
    .status-dot.a00 { background: #2563eb; } .status-dot.a04 { background: #dc2626; } .status-dot.a05 { background: #16a34a; }
</style>

<div class="resume-cards">
    <div class="resume-card" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
        <div class="rc-label">Total Project</div>
        <div class="rc-value">{{ $costings->count() }}</div>
    </div>
    <div class="resume-card" style="background: linear-gradient(135deg, #0f766e, #0d9488);">
        <div class="rc-label">Total COGM</div>
        <div class="rc-value">Rp {{ number_format($totalCogm, 0, ',', '.') }}</div>
    </div>
    <div class="resume-card" style="background: linear-gradient(135deg, #7c3aed, #6d28d9);">
        <div class="rc-label">Total Potensial Cost</div>
        <div class="rc-value">Rp {{ number_format($totalPotential, 0, ',', '.') }}</div>
    </div>
</div>

{{-- Summary by Customer --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h3 class="card-title">Ringkasan per Customer</h3></div>
    <div class="material-table-container">
        <table class="cust-table">
            <thead><tr><th>Customer</th><th style="text-align:center;">Projects</th><th style="text-align:right;">Total COGM</th><th style="text-align:right;">Total Potensial</th></tr></thead>
            <tbody>
                @foreach($byCustomer as $c)
                <tr>
                    <td><strong>{{ $c->customer }}</strong></td>
                    <td style="text-align:center;">{{ $c->count }}</td>
                    <td style="text-align:right;">Rp {{ number_format($c->total_cogm, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($c->total_potential, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Detail COGM --}}
<div class="card">
    <div class="card-header"><h3 class="card-title">Detail COGM per Project</h3></div>
    <div class="material-table-container">
        <table class="cust-table" style="min-width: 1100px;">
            <thead>
                <tr>
                    <th>No.</th><th>Customer</th><th>Model</th><th>Assy Name</th><th>Status</th>
                    <th style="text-align:right;">Material</th><th style="text-align:right;">Labor</th>
                    <th style="text-align:right;">Overhead</th><th style="text-align:right;">COGM</th>
                    <th style="text-align:right;">Forecast</th><th style="text-align:right;">Life (Y)</th>
                    <th style="text-align:right;">Potensial Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($costings as $i => $c)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $c->customer }}</td>
                    <td>{{ $c->model }}</td>
                    <td>{{ $c->assy_name }}</td>
                    <td><span class="status-dot {{ strtolower($c->status) }}"></span>{{ $c->status }}</td>
                    <td style="text-align:right;">Rp {{ number_format($c->material, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($c->labor, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($c->overhead, 0, ',', '.') }}</td>
                    <td style="text-align:right; font-weight:700;">Rp {{ number_format($c->cogm, 0, ',', '.') }}</td>
                    <td style="text-align:right;">{{ number_format($c->forecast, 0, ',', '.') }}</td>
                    <td style="text-align:right;">{{ $c->project_period }}</td>
                    <td style="text-align:right; font-weight:700;">Rp {{ number_format($c->potential, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
