@extends('layouts.app')
@section('title', 'Analisis Tren')
@section('page-title', 'Analisis Tren Biaya')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Menu Utama</a>
    <span class="breadcrumb-separator">/</span>
    <span>Analisis Tren</span>
@endsection

@section('content')
<style>
    .tren-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    .tren-chart-box { background: #fff; border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid var(--slate-200); }
    .tren-chart-title { font-size: 0.95rem; font-weight: 700; color: var(--slate-800); margin-bottom: 1rem; }
    .bar-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; font-size: 0.78rem; }
    .bar-label { width: 70px; text-align: right; color: var(--slate-600); font-weight: 600; flex-shrink: 0; }
    .bar-track { flex: 1; height: 22px; background: var(--slate-100); border-radius: 4px; overflow: hidden; position: relative; }
    .bar-fill { height: 100%; border-radius: 4px; display: flex; align-items: center; padding-left: 6px; font-size: 0.7rem; font-weight: 700; color: #fff; min-width: 20px; transition: width 0.4s; }
    .bar-val { width: 100px; text-align: right; color: var(--slate-700); font-weight: 600; flex-shrink: 0; }
    .rate-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .rate-table th { background: var(--blue-600); color: #fff; padding: 0.6rem 0.75rem; text-align: left; }
    .rate-table td { padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--slate-200); }
    .sparkline { display: flex; align-items: flex-end; gap: 2px; height: 40px; }
    .spark-bar { flex: 1; background: #3b82f6; border-radius: 2px 2px 0 0; min-width: 4px; transition: height 0.3s; }
    .spark-bar:hover { background: #1d4ed8; }
    .legend-row { display: flex; gap: 1.5rem; margin-bottom: 1rem; font-size: 0.78rem; }
    .legend-item { display: flex; align-items: center; gap: 0.3rem; }
    .legend-dot { width: 10px; height: 10px; border-radius: 2px; }
</style>

@php
    $maxTotal = $byPeriod->max('total') ?: 1;
    $maxRate = $exchangeRates->max('usd_to_idr') ?: 1;
@endphp

<div class="tren-grid">
    {{-- COGM Trend --}}
    <div class="tren-chart-box">
        <div class="tren-chart-title">Tren Total COGM per Periode</div>
        <div class="legend-row">
            <div class="legend-item"><div class="legend-dot" style="background:#f97316;"></div>Material</div>
            <div class="legend-item"><div class="legend-dot" style="background:#0f766e;"></div>Labor</div>
            <div class="legend-item"><div class="legend-dot" style="background:#7c3aed;"></div>Overhead</div>
        </div>
        @foreach($byPeriod as $p)
        <div class="bar-row">
            <div class="bar-label">{{ $p->period }}</div>
            <div class="bar-track">
                @php $matW = $maxTotal > 0 ? ($p->material / $maxTotal * 100) : 0; @endphp
                @php $labW = $maxTotal > 0 ? ($p->labor / $maxTotal * 100) : 0; @endphp
                @php $ovhW = $maxTotal > 0 ? ($p->overhead / $maxTotal * 100) : 0; @endphp
                <div style="display:flex; height:100%;">
                    <div class="bar-fill" style="width:{{ $matW }}%; background:#f97316;"></div>
                    <div class="bar-fill" style="width:{{ $labW }}%; background:#0f766e;"></div>
                    <div class="bar-fill" style="width:{{ $ovhW }}%; background:#7c3aed;"></div>
                </div>
            </div>
            <div class="bar-val">Rp {{ number_format($p->total, 0, ',', '.') }}</div>
        </div>
        @endforeach
    </div>

    {{-- Projects per Period --}}
    <div class="tren-chart-box">
        <div class="tren-chart-title">Jumlah Project per Periode</div>
        @php $maxCount = $byPeriod->max('count') ?: 1; @endphp
        @foreach($byPeriod as $p)
        <div class="bar-row">
            <div class="bar-label">{{ $p->period }}</div>
            <div class="bar-track">
                <div class="bar-fill" style="width:{{ $p->count / $maxCount * 100 }}%; background:#2563eb;">{{ $p->count }}</div>
            </div>
            <div class="bar-val">{{ $p->count }} project</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Average COGM --}}
<div class="tren-grid">
    <div class="tren-chart-box">
        <div class="tren-chart-title">Rata-rata COGM per Periode</div>
        @php $maxAvg = $byPeriod->max('avg_cogm') ?: 1; @endphp
        @foreach($byPeriod as $p)
        <div class="bar-row">
            <div class="bar-label">{{ $p->period }}</div>
            <div class="bar-track">
                <div class="bar-fill" style="width:{{ $p->avg_cogm / $maxAvg * 100 }}%; background:#0d9488;"></div>
            </div>
            <div class="bar-val">Rp {{ number_format($p->avg_cogm, 0, ',', '.') }}</div>
        </div>
        @endforeach
    </div>

    {{-- Exchange Rate Trend --}}
    <div class="tren-chart-box">
        <div class="tren-chart-title">Tren Kurs USD/IDR</div>
        @if($exchangeRates->count())
            @php $maxUsd = $exchangeRates->max('usd_to_idr') ?: 1; $minUsd = $exchangeRates->min('usd_to_idr') ?: 0; $range = ($maxUsd - $minUsd) ?: 1; @endphp
            <div class="sparkline" style="height: 80px; margin-bottom: 0.75rem;">
                @foreach($exchangeRates as $r)
                    <div class="spark-bar" style="height: {{ (($r->usd_to_idr - $minUsd) / $range * 80 + 20) }}%; background: #2563eb;" title="{{ $r->period_date->format('M Y') }}: Rp {{ number_format($r->usd_to_idr, 0, ',', '.') }}"></div>
                @endforeach
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--slate-500);">
                <span>{{ $exchangeRates->first()->period_date->format('M Y') }}</span>
                <span>{{ $exchangeRates->last()->period_date->format('M Y') }}</span>
            </div>
        @else
            <p style="color:var(--slate-400); font-size:0.85rem;">Belum ada data kurs.</p>
        @endif
    </div>
</div>

{{-- Detail Table --}}
<div class="card">
    <div class="card-header"><h3 class="card-title">Detail Biaya per Periode</h3></div>
    <div class="material-table-container">
        <table class="rate-table">
            <thead>
                <tr><th>Periode</th><th style="text-align:center;">Projects</th><th style="text-align:right;">Material</th><th style="text-align:right;">Labor</th><th style="text-align:right;">Overhead</th><th style="text-align:right;">Total COGM</th><th style="text-align:right;">Avg COGM</th></tr>
            </thead>
            <tbody>
                @foreach($byPeriod as $p)
                <tr>
                    <td><strong>{{ $p->period }}</strong></td>
                    <td style="text-align:center;">{{ $p->count }}</td>
                    <td style="text-align:right;">Rp {{ number_format($p->material, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($p->labor, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($p->overhead, 0, ',', '.') }}</td>
                    <td style="text-align:right; font-weight:700;">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($p->avg_cogm, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
