@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Costing Per Product Dashboard')

@section('breadcrumb')
    <span>Dashboard</span>
@endsection

@section('header-filters')
    <div class="header-filter">
        <label>Periode:</label>
        <select id="periodFilter" onchange="applyFilters()">
            @foreach($periods as $p)
                <option value="{{ $p }}" {{ $period == $p ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::parse($p . '-01')->format('M Y') }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="header-filter">
        <label>Line:</label>
        <select id="lineFilter" onchange="applyFilters()">
            <option value="all" {{ $line == 'all' ? 'selected' : '' }}>Semua</option>
            @foreach($lines as $l)
                <option value="{{ $l }}" {{ $line == $l ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="header-filter">
        <label>Produk:</label>
        <select id="productFilter" onchange="applyFilters()">
            <option value="all" {{ $productFilter == 'all' ? 'selected' : '' }}>Semua</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" {{ $productFilter == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
            @endforeach
        </select>
    </div>
@endsection

@section('content')
    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Total Cost Bulan Ini</div>
            <div class="kpi-value">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
            <div class="kpi-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/>
                    <path d="M12 18V6"/>
                </svg>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Qty Produksi</div>
            <div class="kpi-value">{{ number_format($totalQty, 0, ',', '.') }} Unit</div>
            <div class="kpi-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                </svg>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Rata-rata Cost Per Unit</div>
            <div class="kpi-value">Rp {{ number_format($avgCostPerUnit, 0, ',', '.') }}</div>
            <div class="kpi-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="20" x2="12" y2="10"/>
                    <line x1="18" y1="20" x2="18" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="16"/>
                </svg>
            </div>
        </div>
        <div class="kpi-card highlight">
            <div class="kpi-label">Produk Tertinggi Cost Per Unit</div>
            <div class="kpi-value">
                @if($highestCostProduct)
                    {{ $highestCostProduct->product->name }} - Rp {{ number_format($highestCostProduct->cost_per_unit, 0, ',', '.') }}
                @else
                    -
                @endif
            </div>
            <div class="kpi-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="7"/>
                    <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Charts Row 1 -->
    <div class="charts-grid">
        <!-- Cost Per Unit per Produk -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cost Per Unit per Produk</h3>
            </div>
            <div class="bar-chart">
                @foreach($costPerProduct as $item)
                    <div class="bar-item">
                        <span class="bar-label">{{ $item['name'] }}</span>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: {{ ($item['cost_per_unit'] / $maxCostPerUnit) * 100 }}%">
                                <span class="bar-value">Rp {{ number_format($item['cost_per_unit'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="line-chart-labels" style="margin-top: 1rem; padding-left: 90px;">
                <span>Rp 0</span>
                <span>Rp {{ number_format($maxCostPerUnit / 4, 0, ',', '.') }}</span>
                <span>Rp {{ number_format($maxCostPerUnit / 2, 0, ',', '.') }}</span>
                <span>Rp {{ number_format($maxCostPerUnit * 3 / 4, 0, ',', '.') }}</span>
                <span>Rp {{ number_format($maxCostPerUnit, 0, ',', '.') }}</span>
            </div>
        </div>
        
        <!-- Komposisi Biaya per Produk -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Komposisi Biaya per Produk</h3>
            </div>
            <div class="stacked-bar-chart">
                @foreach($materialBreakdown as $item)
                    <div class="stacked-bar-item">
                        <span class="bar-label">{{ $item['name'] }}</span>
                        <div class="stacked-bar-container">
                            <div class="stacked-segment material" style="width: {{ $item['material_pct'] }}%"></div>
                            <div class="stacked-segment labor" style="width: {{ $item['labor_pct'] }}%"></div>
                            <div class="stacked-segment overhead" style="width: {{ $item['overhead_pct'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color material"></div>
                    <span>Material Cost</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color labor"></div>
                    <span>Labor Cost</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color overhead"></div>
                    <span>Overhead Cost</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Section -->
    <div class="bottom-grid">
        <!-- Jumlah Produk Tercosting per Bulan -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Jumlah Produk Tercosting per Bulan</h3>
                <span style="font-size: 1.5rem; font-weight: 700; color: var(--blue-400);">{{ $costingData->count() }} Produk</span>
            </div>
            <div class="products-chart">
                @php
                    $months = ['Jul\'24', 'Aug\'24', 'Sep\'24', 'Okt\'24', 'Jan\'25'];
                    $values = [40, 43, 44, 42, 45];
                    $maxVal = max($values);
                @endphp
                @foreach($months as $index => $month)
                    <div class="products-bar">
                        <div class="products-bar-fill" style="height: {{ ($values[$index] / $maxVal) * 100 }}px">
                            <span class="products-bar-value">{{ $values[$index] }}</span>
                        </div>
                        <span class="products-bar-label">{{ $month }}</span>
                    </div>
                @endforeach
            </div>
            <div class="material-info" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--slate-700);">
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 600; color: var(--slate-400); margin-bottom: 0.5rem;">
                    <span>Produk</span>
                    <span>Bedah Material</span>
                </div>
                @foreach($costPerProduct->take(4) as $item)
                    <div class="material-info-item">
                        <span class="material-info-product">{{ $item['name'] }}</span>
                        <span class="material-info-list">
                            @if($loop->index == 0)
                                • Copper (40%)<br>• Steel (30%)
                            @elseif($loop->index == 1)
                                • IC Chips (35%)<br>• Copper (30%)
                            @elseif($loop->index == 2)
                                • Copper (60%)<br>• EVC (35%)
                            @else
                                • Plastic (35%)<br>• Spring (25%)
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Trend Cost Per Unit per Bulan -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Trend Cost Per Unit per Bulan</h3>
            </div>
            <div class="line-chart-container">
                <div class="line-chart-y-labels">
                    <span>Rp 0</span>
                    <span>Rp 200k</span>
                    <span>Rp 300k</span>
                </div>
                <svg class="line-chart-svg" viewBox="0 0 400 150" preserveAspectRatio="xMidYMid meet" style="padding-left: 40px;">
                    <defs>
                        <linearGradient id="lineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:var(--blue-400);stop-opacity:0.3" />
                            <stop offset="100%" style="stop-color:var(--blue-400);stop-opacity:0" />
                        </linearGradient>
                    </defs>
                    <!-- Grid lines -->
                    <line x1="0" y1="30" x2="360" y2="30" class="line-chart-grid" stroke="var(--slate-700)"/>
                    <line x1="0" y1="75" x2="360" y2="75" class="line-chart-grid" stroke="var(--slate-700)"/>
                    <line x1="0" y1="120" x2="360" y2="120" class="line-chart-grid" stroke="var(--slate-700)"/>
                    
                    <!-- Area fill -->
                    <path class="line-chart-area" d="M0,90 L72,85 L144,80 L216,75 L288,72 L360,70 L360,120 L0,120 Z"/>
                    
                    <!-- Line -->
                    <polyline class="line-chart-line" points="0,90 72,85 144,80 216,75 288,72 360,70"/>
                    
                    <!-- Dots -->
                    <circle class="line-chart-dot" cx="0" cy="90" r="5"/>
                    <circle class="line-chart-dot" cx="72" cy="85" r="5"/>
                    <circle class="line-chart-dot" cx="144" cy="80" r="5"/>
                    <circle class="line-chart-dot" cx="216" cy="75" r="5"/>
                    <circle class="line-chart-dot" cx="288" cy="72" r="5"/>
                    <circle class="line-chart-dot" cx="360" cy="70" r="5"/>
                </svg>
            </div>
            <div class="line-chart-labels" style="padding-left: 40px;">
                <span>Jul'24</span>
                <span>Aug'24</span>
                <span>Sep'24</span>
                <span>Okt'24</span>
                <span>Nov'24</span>
                <span>Jan'25</span>
            </div>
            
            <!-- Summary Table -->
            <table class="data-table" style="margin-top: 1.5rem;">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th class="text-right">Total Cost</th>
                        <th class="text-right">Cost Per Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($costingData->take(3) as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td class="text-right">Rp {{ number_format($item->total_cost, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item->cost_per_unit, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Top 5 Customer -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top 5 Customer (by Revenue)</h3>
            </div>
            <div style="margin-bottom: 1.5rem;">
                @foreach($topCustomers as $customer)
                    <div class="customer-bar-item">
                        <span class="customer-name">{{ $customer['name'] }}</span>
                        <div class="customer-bar-container">
                            <div class="customer-bar-fill" style="width: {{ ($customer['revenue'] / $maxRevenue) * 100 }}%"></div>
                        </div>
                        <span class="customer-value">Rp {{ number_format($customer['revenue'] / 1000000, 0, ',', '.') }}M</span>
                    </div>
                @endforeach
            </div>
            
            <!-- Customer Detail Table -->
            <div style="border-top: 1px solid var(--slate-700); padding-top: 1rem;">
                <h4 style="font-size: 0.875rem; color: var(--slate-300); margin-bottom: 0.75rem;">Top 5 Customer (by Revenue)</h4>
                @foreach($topCustomers->take(2) as $customer)
                    <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--slate-700);">
                        <span style="color: var(--slate-200);">{{ $customer['name'] }}</span>
                        <span style="color: white; font-weight: 600;">Rp {{ number_format($customer['revenue'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function applyFilters() {
        const period = document.getElementById('periodFilter').value;
        const line = document.getElementById('lineFilter').value;
        const product = document.getElementById('productFilter').value;
        
        const params = new URLSearchParams();
        params.set('period', period);
        params.set('line', line);
        params.set('product', product);
        
        window.location.href = '{{ route("dashboard") }}?' + params.toString();
    }
    
    // Number formatting helper
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    }
</script>
@endsection
