@extends('layouts.app')

@section('title', 'Form Input Costing')
@section('page-title', 'Form Input Costing')

@section('breadcrumb')
    <a href="{{ route('dashboard', absolute: false) }}">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <span>Form Input Costing</span>
@endsection

@section('content')
    <style>
        /* Hide spin buttons for chrome/safari/edge/opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Hide spin buttons for firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }

        .form-page {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            min-width: 0;
        }

        .form-page .form-section {
            margin-bottom: 0;
        }

        .form-page .card {
            border: 1px solid var(--slate-200);
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .form-page .form-grid {
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        }

        .form-page .form-grid-2 {
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
            gap: 1.5rem;
            align-items: start;
        }

        .form-page .param-grid,
        .form-page .cost-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .form-page .form-group,
        .form-page .form-grid > *,
        .form-page .form-grid-2 > * {
            min-width: 0;
        }

        .form-page .form-input,
        .form-page .form-select {
            width: 100%;
        }

        .form-page .quantity-with-options {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 0.35rem;
            width: 100%;
            min-width: 0;
        }

        .form-page .quantity-with-options .quantity-value {
            flex: 1 1 0;
            min-width: 0;
        }

        .form-page .quantity-with-options .quantity-uom {
            flex: 0 0 82px;
            min-width: 82px;
        }

        .form-page .quantity-with-options .quantity-basis {
            flex: 0 0 118px;
            min-width: 118px;
        }

        .form-page .quantity-with-options .quantity-uom,
        .form-page .quantity-with-options .quantity-basis {
            font-size: 0.75rem;
            padding-left: 0.45rem;
            padding-right: 1.5rem;
        }

        .form-page .calc-box {
            margin-top: 1rem !important;
        }

        .form-page .material-table-container {
            max-width: 100%;
            overflow: auto;
            border: 1px solid var(--slate-200);
            border-radius: 1rem;
            background: white;
        }

        .form-page .material-table {
            min-width: 1360px;
        }

        .form-page .material-table th {
            position: static;
            padding: 0.65rem 0.45rem;
            font-size: 0.65rem;
        }

        .form-page .material-table td {
            padding: 0.4rem 0.35rem;
        }

        .form-page .material-table .form-input,
        .form-page .material-table .form-select {
            min-width: 0;
            padding: 0.5rem 0.6rem;
            font-size: 0.75rem;
        }

        .form-page .material-table .part-no {
            min-width: 120px;
        }

        .form-page .material-table .id-code,
        .form-page .material-table .pro-code,
        .form-page .material-table .supplier {
            min-width: 96px;
        }

        .form-page .material-table .part-name {
            min-width: 160px;
        }

        .form-page .material-table .qty-req,
        .form-page .material-table .qty-moq,
        .form-page .material-table .amount1,
        .form-page .material-table .unit-price-basis,
        .form-page .material-table .import-tax {
            min-width: 84px;
            width: 84px !important;
        }

        .form-page .material-table .unit,
        .form-page .material-table .currency,
        .form-page .material-table .cn-type {
            min-width: 74px;
        }

        .form-page .cycle-table-container {
            max-width: 100%;
            overflow: auto;
            border: 1px solid var(--slate-200);
            border-radius: 1rem;
            background: white;
        }

        .form-page .cycle-table {
            min-width: 1100px;
        }

        .form-page .cycle-table th {
            position: static;
            padding: 0.65rem 0.45rem;
            font-size: 0.65rem;
        }

        .form-page .cycle-table td {
            padding: 0.4rem 0.35rem;
        }

        .form-page .cycle-table .form-input,
        .form-page .cycle-table .form-select {
            min-width: 0;
            padding: 0.5rem 0.6rem;
            font-size: 0.75rem;
        }

        .form-page .cycle-table .ct-process {
            min-width: 260px;
        }

        .form-page .cycle-table .ct-qty,
        .form-page .cycle-table .ct-hour,
        .form-page .cycle-table .ct-sec,
        .form-page .cycle-table .ct-sec-per,
        .form-page .cycle-table .ct-cost-sec,
        .form-page .cycle-table .ct-cost-unit {
            min-width: 110px;
            text-align: right;
        }

        .form-page .cycle-table .ct-cost-sec,
        .form-page .cycle-table .ct-cost-unit {
            background: #a3e635;
            font-weight: 600;
            color: #1f2937;
        }

        .form-page .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            flex-wrap: wrap;
        }

        @media (min-width: 1081px) {
            .form-page .quantity-group {
                grid-column: 1;
                grid-row: 2;
            }

            .form-page .project-life-group {
                grid-column: 2;
                grid-row: 2;
            }

            .form-page .plant-group {
                grid-column: 3;
                grid-row: 2;
            }

            .form-page .period-group {
                grid-column: 4;
                grid-row: 2;
            }
        }

        @media (max-width: 1400px) {
            .form-page .form-grid {
                grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            }

            .form-page .material-table {
                min-width: 1240px;
            }

            .form-page .cycle-table {
                min-width: 1040px;
            }
        }

        @media (max-width: 1080px) {
            .form-page .form-grid-2,
            .form-page .param-grid,
            .form-page .cost-grid {
                grid-template-columns: 1fr !important;
            }

            .form-page .quantity-with-options {
                gap: 0.4rem;
            }
        }

        @media (max-width: 768px) {
            .form-page {
                gap: 1rem;
            }

            .form-page .card {
                padding: 1rem;
            }

            .form-page .material-table {
                min-width: 1120px;
            }

            .form-page .cycle-table {
                min-width: 980px;
            }

            .form-page .form-actions {
                flex-direction: column-reverse;
            }

            .form-page .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    @if(session('success'))
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="form-page">
    <form action="{{ route('costing.store', absolute: false) }}" method="POST" id="costingForm">
        @csrf

        <!-- Section A: Filter & Header -->
        <div class="card form-section">
            <div class="form-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                </svg>
                Informasi Project
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Business Categories</label>
                    <select name="product_id" class="form-select" id="productInput" required>
                        <option value="">-- Pilih Business Categories --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ ($costingData && $costingData->product_id == $product->id) ? 'selected' : '' }}>
                                {{ $product->code }} - {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select" id="customerInput" required>
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ ($costingData && $costingData->customer_id == $customer->id) ? 'selected' : '' }}>
                                {{ $customer->code }} - {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-input" placeholder="Model"
                        value="{{ $costingData->model ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Assy No.</label>
                    <input type="text" name="assy_no" class="form-input" placeholder="Assy No."
                        value="{{ $costingData->assy_no ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Assy Name</label>
                    <input type="text" name="assy_name" class="form-input" placeholder="Assy Name"
                        value="{{ $costingData->assy_name ?? '' }}">
                </div>
                <div class="form-group quantity-group">
                    <label class="form-label">Quantity</label>
                    <div class="quantity-with-options">
                        @php
                            $forecastValue = (int) old('forecast', $costingData->forecast ?? 2000);
                        @endphp
                        <input type="hidden" name="forecast" id="forecast" value="{{ $forecastValue }}">
                        <input type="text" class="form-input quantity-value" id="forecastDisplay"
                            value="{{ number_format($forecastValue, 0, ',', '.') }}" inputmode="numeric"
                            required placeholder="2.000">
                        <select name="forecast_uom" class="form-select quantity-uom">
                            <option value="PCE" {{ old('forecast_uom', 'PCE') == 'PCE' ? 'selected' : '' }}>PCE</option>
                            <option value="Set" {{ old('forecast_uom') == 'Set' ? 'selected' : '' }}>Set</option>
                        </select>
                        <select name="forecast_basis" class="form-select quantity-basis">
                            <option value="per_month" {{ old('forecast_basis', 'per_month') == 'per_month' ? 'selected' : '' }}>Per Bulan</option>
                            <option value="per_year" {{ old('forecast_basis') == 'per_year' ? 'selected' : '' }}>Per Tahun</option>
                        </select>
                    </div>
                </div>
                <div class="form-group project-life-group">
                    <label class="form-label">Product's Life</label>
                    <input type="number" name="project_period" class="form-input" id="projectPeriod"
                        value="{{ $costingData->project_period ?? 2 }}" required>
                </div>
                <div class="form-group plant-group">
                    <label class="form-label">Plant</label>
                    <select name="line" class="form-select">
                        <option value="">-- Pilih Plant --</option>
                        @foreach($lines as $line)
                            <option value="{{ $line }}">{{ $line }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group period-group">
                    <label class="form-label">Periode</label>
                    <select name="period" class="form-select" id="periodInput">
                        <option value="">-- Pilih Periode --</option>
                        @for($i = 0; $i < 12; $i++)
                            @php $date = now()->subMonths($i); @endphp
                            <option value="{{ $date->format('Y-m') }}" {{ ($costingData && $costingData->period == $date->format('Y-m')) ? 'selected' : '' }}>
                                {{ $date->format('M Y') }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>


        <!-- Section B: Production Parameters & Actual Costs -->
        <div class="card form-section">
            <div class="form-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg>
                Rates
            </div>
            <div class="form-grid param-grid">
                <div class="form-group">
                    <label class="form-label">USD</label>
                    <input type="number" name="exchange_rate_usd" class="form-input" id="rateUSD"
                        value="{{ $costingData->exchange_rate_usd ?? 15500 }}" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">JPY</label>
                    <input type="number" name="exchange_rate_jpy" class="form-input" id="rateJPY"
                        value="{{ $costingData->exchange_rate_jpy ?? 103 }}" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">IDR</label>
                    <input type="number" name="exchange_rate_idr" class="form-input" id="rateIDR" value="1"
                        disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">LME Rate</label>
                    <input type="number" name="lme_rate" class="form-input" id="lmeRate"
                        value="{{ $costingData->lme_rate ?? '' }}" step="0.01" placeholder="8500">
                </div>
            </div>
        </div>

        <!-- Section C: Actual Costs -->
        <div class="card form-section">
            <div class="form-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 1v22" />
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>
                Biaya Actual
            </div>
            <div class="form-grid cost-grid">
                <div class="form-group">
                    <label class="form-label">Material Cost (IDR)</label>
                    <input type="number" name="material_cost" class="form-input" id="materialCost"
                        value="{{ $costingData->material_cost ?? '' }}" required placeholder="0"
                        onchange="calculateTotals()">
                </div>
                <div class="form-group">
                    <label class="form-label">Labor Cost (IDR)</label>
                    <input type="number" name="labor_cost" class="form-input" id="laborCost"
                        value="{{ $costingData->labor_cost ?? '' }}" required placeholder="0"
                        onchange="calculateTotals()">
                </div>
                <div class="form-group">
                    <label class="form-label">Overhead Cost (IDR)</label>
                    <input type="number" name="overhead_cost" class="form-input" id="overheadCost"
                        value="{{ $costingData->overhead_cost ?? '' }}" required placeholder="0"
                        onchange="calculateTotals()">
                </div>
                <div class="form-group">
                    <label class="form-label">Scrap Cost (IDR)</label>
                    <input type="number" name="scrap_cost" class="form-input" id="scrapCost"
                        value="{{ $costingData->scrap_cost ?? '' }}" required placeholder="0"
                        onchange="calculateTotals()">
                </div>
                <div class="form-group">
                    <label class="form-label">Revenue (IDR)</label>
                    <input type="number" name="revenue" class="form-input" id="revenue"
                        value="{{ $costingData->revenue ?? '' }}" required placeholder="0"
                        onchange="calculateTotals()">
                </div>
                <div class="form-group">
                    <label class="form-label">Qty Good</label>
                    <input type="number" name="qty_good" class="form-input" id="qtyGood"
                        value="{{ $costingData->qty_good ?? '' }}" required placeholder="0"
                        onchange="calculateTotals()">
                </div>
            </div>

            <div class="calc-box" style="margin-top: 1.5rem;">
                <div class="calc-item">
                    <span class="calc-label">Total Cost</span>
                    <span class="calc-value" id="calcTotalCost">Rp 0</span>
                </div>
                <div class="calc-item">
                    <span class="calc-label">Cost Per Unit</span>
                    <span class="calc-value" id="calcCostPerUnit">Rp 0</span>
                </div>
                <div class="calc-item">
                    <span class="calc-label">Margin</span>
                    <span class="calc-value" id="calcMargin">0%</span>
                </div>
            </div>
        </div>

        <!-- Section D: Material Breakdown Table -->
        <div class="card form-section">
            <div class="form-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                </svg>
                Material
                <button type="button" class="btn btn-secondary" style="margin-left: auto;" onclick="addMaterialRow()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Tambah Baris
                </button>
            </div>

            <div class="material-table-container">
                <table class="material-table" id="materialTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Part No</th>
                            <th>ID Code</th>
                            <th>Part Name</th>
                            <th style="width: 7rem;">Qty Req</th>
                            <th>Unit</th>
                            <th>Pro Code</th>
                            <th>Amount 1</th>
                            <th>Unit Price (Basis)</th>
                            <th>Currency</th>
                            <th style="width: 7rem;">Qty MOQ</th>
                            <th>C/N</th>
                            <th>Supplier</th>
                            <th>Import Tax (%)</th>
                            <th>Multiply Factor</th>
                            <th>Amount 2</th>
                            <th>Currency 2</th>
                            <th>Unit Price 2</th>
                            <th>Total Price (IDR)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="materialTableBody">
                        @if($materialBreakdowns->count() > 0)
                            @foreach($materialBreakdowns as $index => $breakdown)
                                <tr data-row="{{ $index }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td><input type="text" class="form-input part-no" name="materials[{{ $index }}][part_no]"
                                            value="{{ $breakdown->material->part_no ?? '' }}" placeholder="Part No"></td>
                                    <td><input type="text" class="form-input id-code" name="materials[{{ $index }}][id_code]"
                                            value="{{ $breakdown->material->id_code ?? '' }}" placeholder="ID Code"></td>
                                    <td><input type="text" class="form-input part-name" name="materials[{{ $index }}][part_name]"
                                            value="{{ $breakdown->material->part_name ?? '' }}" placeholder="Part Name"></td>
                                    <td><input type="number" class="form-input w-28 qty-req" name="materials[{{ $index }}][qty_req]"
                                            value="{{ $breakdown->qty_req }}" step="0.0001" onchange="calculateRow(this)"></td>
                                    <td><input type="text" class="form-input unit" name="materials[{{ $index }}][unit]"
                                            value="{{ $breakdown->material->unit ?? 'PCS' }}" placeholder="Unit"></td>
                                    <td><input type="text" class="form-input pro-code" name="materials[{{ $index }}][pro_code]"
                                            value="{{ $breakdown->material->pro_code ?? '' }}" placeholder="Pro Code"></td>
                                    <td><input type="number" class="form-input amount1" name="materials[{{ $index }}][amount1]" value="{{ $breakdown->amount1 }}"
                                            step="0.0001" onchange="calculateRow(this)"></td>
                                    <td><input type="number" class="form-input unit-price-basis" name="materials[{{ $index }}][unit_price_basis]"
                                            value="{{ $breakdown->unit_price_basis }}" placeholder="Unit Price"
                                            step="0.0001" onchange="calculateRow(this)">
                                    </td>
                                    <td>
                                        <select class="form-select currency" name="materials[{{ $index }}][currency]" onchange="calculateRow(this)">
                                            <option value="IDR" {{ $breakdown->currency == 'IDR' ? 'selected' : '' }}>IDR</option>
                                            <option value="USD" {{ $breakdown->currency == 'USD' ? 'selected' : '' }}>USD</option>
                                            <option value="JPY" {{ $breakdown->currency == 'JPY' ? 'selected' : '' }}>JPY</option>
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-input w-28 qty-moq" name="materials[{{ $index }}][qty_moq]" value="{{ $breakdown->qty_moq }}"
                                            step="0.0001" onchange="calculateRow(this)"></td>
                                    <td>
                                        <select class="form-select cn-type" name="materials[{{ $index }}][cn_type]" onchange="calculateRow(this)">
                                            <option value="N" {{ $breakdown->cn_type == 'N' ? 'selected' : '' }}>N</option>
                                            <option value="C" {{ $breakdown->cn_type == 'C' ? 'selected' : '' }}>C</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-input supplier" name="materials[{{ $index }}][supplier]"
                                            value="{{ $breakdown->material->supplier_name ?? '' }}" placeholder="Supplier"></td>
                                    <td><input type="number" class="form-input import-tax" name="materials[{{ $index }}][import_tax]"
                                            value="{{ $breakdown->import_tax_percent }}" step="0.01" onchange="calculateRow(this)">
                                    </td>
                                    <td class="calculated multiply-factor">1.0000</td>
                                    <td class="calculated amount2">{{ number_format($breakdown->amount2 ?? 0, 4) }}</td>
                                    <td class="calculated currency2">{{ $breakdown->currency ?? 'IDR' }}</td>
                                    <td class="calculated unit-price2">{{ $breakdown->material->unit ?? 'PCS' }}</td>
                                    <td class="calculated total-price">Rp 0</td>
                                    <td>
                                        <button type="button" class="btn btn-secondary" onclick="removeRow(this)"
                                            style="padding: 0.5rem;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <!-- Default empty rows -->
                            @for($i = 0; $i < 5; $i++)
                                <tr data-row="{{ $i }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td><input type="text" class="form-input part-no" name="materials[{{ $i }}][part_no]" value=""
                                            placeholder="Part No"></td>
                                    <td><input type="text" class="form-input id-code" name="materials[{{ $i }}][id_code]" value=""
                                            placeholder="ID Code"></td>
                                    <td><input type="text" class="form-input part-name" name="materials[{{ $i }}][part_name]"
                                            value="" placeholder="Part Name"></td>
                                    <td><input type="number" class="form-input w-28 qty-req" name="materials[{{ $i }}][qty_req]"
                                            value="0" step="0.0001" onchange="calculateRow(this)"></td>
                                    <td><input type="text" class="form-input unit" name="materials[{{ $i }}][unit]" value="PCS"
                                            placeholder="Unit"></td>
                                    <td><input type="text" class="form-input pro-code" name="materials[{{ $i }}][pro_code]" value=""
                                            placeholder="Pro Code"></td>
                                    <td><input type="number" class="form-input amount1" value="0" step="0.0001"
                                            onchange="calculateRow(this)"></td>
                                    <td><input type="text" class="form-input unit-price-basis" value="" placeholder="Unit Price"
                                            onchange="calculateRow(this)"></td>
                                    <td>
                                        <select class="form-select currency" onchange="calculateRow(this)">
                                            <option value="IDR">IDR</option>
                                            <option value="USD">USD</option>
                                            <option value="JPY">JPY</option>
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-input w-28 qty-moq" value="0" step="0.0001"
                                            onchange="calculateRow(this)"></td>
                                    <td>
                                        <select class="form-select cn-type" onchange="calculateRow(this)">
                                            <option value="N">N</option>
                                            <option value="C">C</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-input supplier" name="materials[{{ $i }}][supplier]" value=""
                                            placeholder="Supplier"></td>
                                    <td><input type="number" class="form-input import-tax" value="0" step="0.01"
                                            onchange="calculateRow(this)"></td>
                                    <td class="calculated multiply-factor">1.0000</td>
                                    <td class="calculated amount2">0.0000</td>
                                    <td class="calculated currency2">IDR</td>
                                    <td class="calculated unit-price2">PCS</td>
                                    <td class="calculated total-price">Rp 0</td>
                                    <td>
                                        <button type="button" class="btn btn-secondary" onclick="removeRow(this)"
                                            style="padding: 0.5rem;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endfor
                        @endif
                    </tbody>
                    <tfoot>
                        <tr style="background: var(--slate-700);">
                            <td colspan="18" style="text-align: right; font-weight: 600;">Total Material dari Tabel:</td>
                            <td class="calculated" id="tableTotalMaterial"
                                style="font-weight: 700; color: var(--blue-300);">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>


        </div>

        <!-- Section E: Cycle Time -->
        <div class="card form-section">
            <div class="form-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 8v4l3 3" />
                    <circle cx="12" cy="12" r="10" />
                </svg>
                Cycle Time
                <button type="button" class="btn btn-secondary" style="margin-left: auto;" onclick="addCycleTimeRow()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Tambah Baris
                </button>
            </div>

            <div class="cycle-table-container">
                <table class="cycle-table" id="cycleTimeTable">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>PROCESS</th>
                            <th>QTY</th>
                            <th>TIME (HOUR)</th>
                            <th>TIME (SEC)</th>
                            <th>TIME (SEC) / 1 Qty</th>
                            <th>Cost / SEC</th>
                            <th>Cost / Unit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cycleTimeTableBody">
                        @php
                            $cycleTimes = old('cycle_times', $costingData->cycle_times ?? []);
                            if (!is_array($cycleTimes)) {
                                $cycleTimes = [];
                            }

                            if (count($cycleTimes) === 0 && isset($cycleTimeTemplates) && $cycleTimeTemplates->count() > 0) {
                                $cycleTimes = $cycleTimeTemplates->map(function ($template) {
                                    return [
                                        'process' => $template->process,
                                    ];
                                })->toArray();
                            }

                            $cycleTemplateProcesses = ($cycleTimeTemplates ?? collect())->pluck('process')->filter()->values();

                            $initialCycleCount = count($cycleTimes) > 0 ? count($cycleTimes) : 5;
                        @endphp
                        @if(count($cycleTimes) > 0)
                            @foreach($cycleTimes as $index => $cycle)
                                <tr data-cycle-row="{{ $index }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <select class="form-select ct-process" name="cycle_times[{{ $index }}][process]">
                                            <option value="">-- Pilih Process --</option>
                                            @foreach(($cycleTimeTemplates ?? collect()) as $template)
                                                <option value="{{ $template->process }}" {{ (($cycle['process'] ?? '') === $template->process) ? 'selected' : '' }}>
                                                    {{ $template->process }}
                                                </option>
                                            @endforeach
                                            @if(!empty($cycle['process'] ?? '') && !$cycleTemplateProcesses->contains($cycle['process']))
                                                <option value="{{ $cycle['process'] }}" selected>{{ $cycle['process'] }}</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-qty"
                                            name="cycle_times[{{ $index }}][qty]"
                                            value="{{ $cycle['qty'] ?? '' }}" step="0.0001" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-hour"
                                            name="cycle_times[{{ $index }}][time_hour]"
                                            value="{{ $cycle['time_hour'] ?? '' }}" step="0.0001" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-sec"
                                            name="cycle_times[{{ $index }}][time_sec]"
                                            value="{{ isset($cycle['time_sec']) && $cycle['time_sec'] !== '' ? round((float) $cycle['time_sec']) : '' }}" step="1" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-sec-per"
                                            name="cycle_times[{{ $index }}][time_sec_per_qty]"
                                            value="{{ isset($cycle['time_sec_per_qty']) && $cycle['time_sec_per_qty'] !== '' ? round((float) $cycle['time_sec_per_qty']) : '' }}" step="1" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-cost-sec"
                                            name="cycle_times[{{ $index }}][cost_per_sec]"
                                            value="{{ $cycle['cost_per_sec'] ?? '10.33' }}" step="0.0001" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-cost-unit"
                                            name="cycle_times[{{ $index }}][cost_per_unit]"
                                            value="{{ isset($cycle['cost_per_unit']) && $cycle['cost_per_unit'] !== '' ? round((float) $cycle['cost_per_unit']) : '' }}" step="1" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-secondary" onclick="removeCycleTimeRow(this)"
                                            style="padding: 0.5rem;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @for($i = 0; $i < 5; $i++)
                                <tr data-cycle-row="{{ $i }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <select class="form-select ct-process" name="cycle_times[{{ $i }}][process]">
                                            <option value="">-- Pilih Process --</option>
                                            @foreach(($cycleTimeTemplates ?? collect()) as $template)
                                                <option value="{{ $template->process }}">{{ $template->process }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-qty"
                                            name="cycle_times[{{ $i }}][qty]" value="" step="0.0001" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-hour"
                                            name="cycle_times[{{ $i }}][time_hour]" value="" step="0.0001" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-sec"
                                            name="cycle_times[{{ $i }}][time_sec]" value="" step="1" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-sec-per"
                                            name="cycle_times[{{ $i }}][time_sec_per_qty]" value="" step="1" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-cost-sec"
                                            name="cycle_times[{{ $i }}][cost_per_sec]" value="10.33" step="0.0001" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input ct-cost-unit"
                                            name="cycle_times[{{ $i }}][cost_per_unit]" value="" step="1" onchange="calculateCycleRow(this)">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-secondary" onclick="removeCycleTimeRow(this)"
                                            style="padding: 0.5rem;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endfor
                        @endif
                    </tbody>
                    <tfoot>
                        <tr style="background: var(--slate-700);">
                            <td colspan="4" style="text-align: right; font-weight: 600;">Total</td>
                            <td class="calculated" id="cycleTotalSec" style="font-weight: 700; color: var(--blue-300);">0</td>
                            <td></td>
                            <td></td>
                            <td class="calculated" id="cycleTotalCostUnit" style="font-weight: 700; color: var(--blue-300);">0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('dashboard', absolute: false) }}'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
                Batal
            </button>
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
                Simpan Data Costing
            </button>
        </div>
    </form>
    </div>
@endsection

@section('scripts')
    <script>
        // Global variables
        let rowCounter = {{ $materialBreakdowns->count() > 0 ? $materialBreakdowns->count() : 5 }};
        let cycleRowCounter = {{ $initialCycleCount }};

        // Materials data for dynamic selection
        const materials = @json($materials);
        const cycleProcessOptions = @json(($cycleTimeTemplates ?? collect())->pluck('process')->values());

        // Format number as Rupiah
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }

        function formatWholeNumber(number) {
            return String(Math.round(Number(number) || 0));
        }

        function parsePositiveInteger(value) {
            const digits = String(value || '').replace(/[^\d]/g, '');
            if (!digits) return 0;
            return parseInt(digits, 10) || 0;
        }

        function syncForecastHidden() {
            const forecastDisplay = document.getElementById('forecastDisplay');
            const forecastHidden = document.getElementById('forecast');
            if (!forecastDisplay || !forecastHidden) return 0;

            const numericValue = parsePositiveInteger(forecastDisplay.value);
            forecastHidden.value = numericValue;
            return numericValue;
        }

        function formatForecastDisplay() {
            const forecastDisplay = document.getElementById('forecastDisplay');
            if (!forecastDisplay) return;

            const numericValue = syncForecastHidden();
            forecastDisplay.value = numericValue > 0
                ? new Intl.NumberFormat('id-ID').format(numericValue)
                : '';
        }

        // Get exchange rate based on currency
        function getExchangeRate(currency) {
            switch (currency) {
                case 'USD': return parseFloat(document.getElementById('rateUSD').value) || 15500;
                case 'JPY': return parseFloat(document.getElementById('rateJPY').value) || 103;
                default: return 1;
            }
        }

        // Calculate Multiply Factor
        // Logika: IF(qtyReq=0,0, IF(OR(cnFlag="C",(moq/(forecast*period*12*qtyReq/unitDivisor))<1), 1, moq/(forecast*period*12*qtyReq/unitDivisor)))
        function calculateMultiplyFactor(row) {
            const qtyReq = parseFloat(row.querySelector('.qty-req').value) || 0;
            const moq = parseFloat(row.querySelector('.qty-moq').value) || 0;
            const forecast = parseFloat(document.getElementById('forecast').value) || 0;
            const period = parseFloat(document.getElementById('projectPeriod').value) || 0;
            const unit = (row.querySelector('.unit').value || row.querySelector('.unit').textContent || '').toUpperCase();
            const cnFlag = row.querySelector('.cn-type').value;

            // 1) IF(qtyReq=0, return 0)
            if (qtyReq === 0) {
                return 0;
            }

            // 2) Unit divisor: IF(unit="MM", 1000, 1)
            const unitDivisor = (unit === 'MM') ? 1000 : 1;

            // 3) denominator = forecast * period * 12 * qtyReq / unitDivisor
            let denominator = forecast * period * 12 * qtyReq;
            denominator = denominator / unitDivisor;

            // 4) Antisipasi pembagian nol
            if (denominator === 0) {
                return 1;
            }

            // 5) ratio = moq / denominator
            const ratio = moq / denominator;

            // 6) IF(cnFlag="C" OR ratio<1, return 1)
            if (cnFlag === 'C' || ratio < 1) {
                return 1;
            }

            // 7) else return ratio
            return ratio;
        }

        // Helper to parse input values safely
        // STRATEGI: Asumsi User Indonesia
        // 1. Hapus semua TITIK (.) yang biasanya dipakai sebagai pemisah ribuan
        // 2. Ganti KOMA (,) menjadi TITIK (.) sebagai pemisah desimal
        // Contoh: "1.000,50" -> "1000.50"
        function parseInputNumber(value) {
            if (!value) return 0;
            let str = value.toString();

            // 1. Hapus titik (ribuan)
            str = str.replace(/\./g, '');

            // 2. Ganti koma jadi titik (desimal)
            str = str.replace(/,/g, '.');

            return parseFloat(str) || 0;
        }

        // Calculate row total
        function calculateRow(element) {
            const row = element.closest('tr');

            // Debugging
            console.log("Calculating Row:", row.dataset.row);

            // 1. Calculate and set Multiply Factor (S4)
            const multiplyFactor = calculateMultiplyFactor(row);
            row.querySelector('.multiply-factor').textContent = multiplyFactor.toFixed(4);

            // GET INPUTS dengan Mapping Baru:
            // Amount 1 = Harga (Price Base)
            const priceInput = row.querySelector('.amount1').value;
            const priceBase = parseInputNumber(priceInput); // Ini L4

            // Unit Price Basis = Satuan/UOM (Unit)
            // Ambil dari input atau textContent tergantung implementasi (sekarang input text)
            // Kita ambil value karena ini input text
            const uom = (row.querySelector('.unit').value || '').trim().toUpperCase(); // Ini M4 (untuk divisor)

            const importTax = parseFloat(row.querySelector('.import-tax').value) || 0; // R4

            // 2. Calculate Amount 2 (T4) logic
            // Rumus: (MultiplyFactor * (PriceBase + (PriceBase * Tax%))) / Divisor

            // Extra = PriceBase * (Tax / 100)
            const extra = priceBase * (importTax / 100);

            // Base = PriceBase + Extra
            const base = priceBase + extra;

            // Numerator = Multiply Factor * Base
            const numerator = multiplyFactor * base;

            // Unit Divisor: 1000 if UOM is METER/M/MTR/MM (User minta MM divisor 1000 di multiply factor, mungkin di sini juga?)
            // Mengikuti prompt: "IF(OR(M4="METER", M4="M", M4="MTR"), 1000, 1)"
            // Kita tambahkan "MM" juga untuk konsistensi dengan multiply factor jika perlu, tapi ikuti prompt asli dulu.
            // Prompt Amount 2 bilang: "METER", "M", "MTR".
            let unitDivisor = 1;
            if (uom === 'METER' || uom === 'M' || uom === 'MTR' || uom === 'MM') {
                unitDivisor = 1000;
            }

            // Amount 2 = Numerator / Unit Divisor
            const amount2 = (unitDivisor !== 0) ? (numerator / unitDivisor) : 0;

            // Set Amount 2 value to text
            const amount2Element = row.querySelector('.amount2');
            amount2Element.textContent = amount2.toFixed(4);

            // 3. Calculate Total Price
            // Total = Qty * Amount 2 * Exchange Rate
            const qty = parseFloat(row.querySelector('.qty-req').value) || 0;
            const currency = row.querySelector('.currency').value;
            const exchangeRate = getExchangeRate(currency);

            // Sync Currency 2 with Currency
            row.querySelector('.currency2').textContent = currency;

            // Sync Unit Price 2 with UOM (Unit Price Basis)
            // Request terakhir "Unit Price 2 diambil dari unit". 
            // Sekarang "unit" kita ada di kolom "Unit Price Basis".
            row.querySelector('.unit-price2').textContent = uom; // Note: classnya mungkin typo di html saya sebelumnya? Cek di replace sebelumnya saya pakai .unit-price2

            // Total 
            const total = qty * amount2 * exchangeRate;

            row.querySelector('.total-price').textContent = formatRupiah(total);

            // Recalculate all totals
            calculateTableTotal();
        }

        // Calculate table total
        function calculateTableTotal(syncMaterialCost = true) {
            let total = 0;
            const rows = document.querySelectorAll('#materialTableBody tr');

            rows.forEach(row => {
                const totalText = row.querySelector('.total-price').textContent;
                const value = parseFloat(totalText.replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
                total += value;
            });

            // Update Footer Total
            document.getElementById('tableTotalMaterial').textContent = formatRupiah(total);

            // AUTO-FILL Material Cost in Section B
            const materialCostInput = document.getElementById('materialCost');
            if (materialCostInput && syncMaterialCost) {
                materialCostInput.value = total; // Set raw value
                // Trigger calculation of Section B totals
                calculateTotals(false);
            }

            return total;
        }



        // Calculate totals in Section B
        function calculateTotals(recalculateMaterialTable = true) {
            const materialCost = parseFloat(document.getElementById('materialCost').value) || 0;
            const laborCost = parseFloat(document.getElementById('laborCost').value) || 0;
            const overheadCost = parseFloat(document.getElementById('overheadCost').value) || 0;
            const scrapCost = parseFloat(document.getElementById('scrapCost').value) || 0;
            const revenue = parseFloat(document.getElementById('revenue').value) || 0;
            const qtyGood = parseFloat(document.getElementById('qtyGood').value) || 0;

            const totalCost = materialCost + laborCost + overheadCost + scrapCost;
            const costPerUnit = qtyGood > 0 ? totalCost / qtyGood : 0;
            const margin = revenue > 0 ? ((revenue - totalCost) / revenue) * 100 : 0;

            document.getElementById('calcTotalCost').textContent = formatRupiah(totalCost);
            document.getElementById('calcCostPerUnit').textContent = formatRupiah(costPerUnit);

            const marginElement = document.getElementById('calcMargin');
            marginElement.textContent = margin.toFixed(2) + '%';
            marginElement.className = 'calc-value ' + (margin >= 0 ? 'positive' : 'negative');

            // Revalidate material cost
            if (recalculateMaterialTable) {
                calculateTableTotal(false);
            }
        }

        // Update material info when dropdown changes
        function updateMaterialInfo(select) {
            const row = select.closest('tr');
            const option = select.options[select.selectedIndex];

            row.querySelector('.id-code').textContent = option.dataset.idcode || '';
            row.querySelector('.part-name').textContent = option.dataset.partname || '';
            row.querySelector('.unit').textContent = option.dataset.unit || 'PCS';
            row.querySelector('.pro-code').textContent = option.dataset.procode || '';
            row.querySelector('.supplier').textContent = option.dataset.supplier || '';

            calculateRow(select);
        }

        // Add new material row
        function addMaterialRow() {
            const tbody = document.getElementById('materialTableBody');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-row', rowCounter);

            newRow.innerHTML = `
                                    <td>${rowCounter + 1}</td>
                                    <td><input type="text" class="form-input part-no" name="materials[${rowCounter}][part_no]" value="" placeholder="Part No"></td>
                                    <td><input type="text" class="form-input id-code" name="materials[${rowCounter}][id_code]" value="" placeholder="ID Code"></td>
                                    <td><input type="text" class="form-input part-name" name="materials[${rowCounter}][part_name]" value="" placeholder="Part Name"></td>
                                    <td><input type="number" class="form-input w-28 qty-req" name="materials[${rowCounter}][qty_req]" value="0" step="0.0001" onchange="calculateRow(this)"></td>
                                    <td><input type="text" class="form-input unit" name="materials[${rowCounter}][unit]" value="PCS" placeholder="Unit"></td>
                                    <td><input type="text" class="form-input pro-code" name="materials[${rowCounter}][pro_code]" value="" placeholder="Pro Code"></td>
                                    <td><input type="number" class="form-input amount1" value="0" step="0.0001" onchange="calculateRow(this)"></td>
                                    <td><input type="text" class="form-input unit-price-basis" value="" placeholder="Unit Price" onchange="calculateRow(this)"></td>
                                    <td><select class="form-select currency" onchange="calculateRow(this)"><option value="IDR">IDR</option><option value="USD">USD</option><option value="JPY">JPY</option></select></td>
                                    <td><input type="number" class="form-input w-28 qty-moq" value="0" step="0.0001" onchange="calculateRow(this)"></td>
                                    <td><select class="form-select cn-type" onchange="calculateRow(this)"><option value="N">N</option><option value="C">C</option></select></td>
                                    <td><input type="text" class="form-input supplier" name="materials[${rowCounter}][supplier]" value="" placeholder="Supplier"></td>
                                    <td><input type="number" class="form-input import-tax" value="0" step="0.01" onchange="calculateRow(this)"></td>
                                    <td class="calculated multiply-factor">1.0000</td>
                        <td class="calculated amount2">0.0000</td>
                        <td class="calculated currency2">IDR</td>
                        <td class="calculated unit-price2">PCS</td>
                                    <td class="calculated total-price">Rp 0</td>
                                    <td><button type="button" class="btn btn-secondary" onclick="removeRow(this)" style="padding: 0.5rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button></td>
                                `;

            tbody.appendChild(newRow);
            rowCounter++;
            renumberRows();
        }

        // Remove row
        function removeRow(button) {
            const row = button.closest('tr');
            row.remove();
            renumberRows();
            calculateTableTotal();
        }

        // Renumber rows
        function renumberRows() {
            const rows = document.querySelectorAll('#materialTableBody tr');
            rows.forEach((row, index) => {
                row.cells[0].textContent = index + 1;
            });
        }

        function calculateCycleRow(element) {
            const row = element.closest('tr');
            if (!row) return;

            const qtyInput = row.querySelector('.ct-qty');
            const hourInput = row.querySelector('.ct-hour');
            const secInput = row.querySelector('.ct-sec');
            const secPerInput = row.querySelector('.ct-sec-per');
            const costSecInput = row.querySelector('.ct-cost-sec');
            const costUnitInput = row.querySelector('.ct-cost-unit');

            const qty = parseFloat(qtyInput.value) || 0;
            let hour = parseFloat(hourInput.value) || 0;
            let sec = parseFloat(secInput.value) || 0;
            const costPerSec = parseFloat(costSecInput.value) || 0;

            if (element.classList.contains('ct-hour')) {
                sec = hour * 3600;
                secInput.value = String(Math.round(sec));
            } else if (element.classList.contains('ct-sec')) {
                hour = sec / 3600;
                hourInput.value = hour.toFixed(6);
            }

            const secPerQty = qty > 0 ? (sec / qty) : 0;
            const costPerUnit = sec * costPerSec;

            secPerInput.value = String(Math.round(secPerQty));

            if (!costUnitInput.value || element.classList.contains('ct-hour') || element.classList.contains('ct-sec') || element.classList.contains('ct-cost-sec')) {
                costUnitInput.value = String(Math.round(costPerUnit));
            }

            calculateCycleTotals();
        }

        function calculateCycleTotals() {
            let totalSec = 0;
            let totalCostUnit = 0;
            const rows = document.querySelectorAll('#cycleTimeTableBody tr');

            rows.forEach((row) => {
                totalSec += parseFloat(row.querySelector('.ct-sec')?.value) || 0;
                totalCostUnit += parseFloat(row.querySelector('.ct-cost-unit')?.value) || 0;
            });

            const totalSecEl = document.getElementById('cycleTotalSec');
            const totalCostUnitEl = document.getElementById('cycleTotalCostUnit');
            if (totalSecEl) {
                totalSecEl.textContent = formatWholeNumber(totalSec);
            }
            if (totalCostUnitEl) {
                totalCostUnitEl.textContent = formatWholeNumber(totalCostUnit);
            }
        }

        function addCycleTimeRow() {
            const tbody = document.getElementById('cycleTimeTableBody');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-cycle-row', cycleRowCounter);

            const escapeHtml = (value) => String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const processOptionsHtml = ['<option value="">-- Pilih Process --</option>']
                .concat(cycleProcessOptions.map((process) => {
                    const escaped = escapeHtml(process);
                    return `<option value="${escaped}">${escaped}</option>`;
                }))
                .join('');

            newRow.innerHTML = `
                <td>${cycleRowCounter + 1}</td>
                <td><select class="form-select ct-process" name="cycle_times[${cycleRowCounter}][process]">${processOptionsHtml}</select></td>
                <td><input type="number" class="form-input ct-qty" name="cycle_times[${cycleRowCounter}][qty]" value="" step="0.0001" onchange="calculateCycleRow(this)"></td>
                <td><input type="number" class="form-input ct-hour" name="cycle_times[${cycleRowCounter}][time_hour]" value="" step="0.0001" onchange="calculateCycleRow(this)"></td>
                <td><input type="number" class="form-input ct-sec" name="cycle_times[${cycleRowCounter}][time_sec]" value="" step="1" onchange="calculateCycleRow(this)"></td>
                <td><input type="number" class="form-input ct-sec-per" name="cycle_times[${cycleRowCounter}][time_sec_per_qty]" value="" step="1" onchange="calculateCycleRow(this)"></td>
                <td><input type="number" class="form-input ct-cost-sec" name="cycle_times[${cycleRowCounter}][cost_per_sec]" value="10.33" step="0.0001" onchange="calculateCycleRow(this)"></td>
                <td><input type="number" class="form-input ct-cost-unit" name="cycle_times[${cycleRowCounter}][cost_per_unit]" value="" step="1" onchange="calculateCycleRow(this)"></td>
                <td><button type="button" class="btn btn-secondary" onclick="removeCycleTimeRow(this)" style="padding: 0.5rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button></td>
            `;

            tbody.appendChild(newRow);
            cycleRowCounter++;
            renumberCycleRows();
        }

        function removeCycleTimeRow(button) {
            const row = button.closest('tr');
            row.remove();
            renumberCycleRows();
            calculateCycleTotals();
        }

        function renumberCycleRows() {
            const rows = document.querySelectorAll('#cycleTimeTableBody tr');
            rows.forEach((row, index) => {
                row.cells[0].textContent = index + 1;
            });
        }

        // Initialize calculations on page load
        document.addEventListener('DOMContentLoaded', function () {
            formatForecastDisplay();
            calculateTotals();

            // Calculate all rows
            const rows = document.querySelectorAll('#materialTableBody tr');
            rows.forEach(row => {
                const input = row.querySelector('.qty-req');
                if (input) calculateRow(input);
            });

            const cycleRows = document.querySelectorAll('#cycleTimeTableBody tr');
            cycleRows.forEach(row => {
                const input = row.querySelector('.ct-hour') || row.querySelector('.ct-sec');
                if (input) calculateCycleRow(input);
            });

            calculateCycleTotals();

            const forecastDisplay = document.getElementById('forecastDisplay');
            if (forecastDisplay) {
                forecastDisplay.addEventListener('input', function () {
                    syncForecastHidden();
                    recalculateAllRows();
                });

                forecastDisplay.addEventListener('blur', function () {
                    formatForecastDisplay();
                    recalculateAllRows();
                });
            }

            const costingForm = document.getElementById('costingForm');
            if (costingForm) {
                costingForm.addEventListener('submit', function () {
                    syncForecastHidden();
                });
            }
        });

        // Recalculate when exchange rates change
        document.getElementById('rateUSD').addEventListener('change', recalculateAllRows);
        document.getElementById('rateJPY').addEventListener('change', recalculateAllRows);
        document.getElementById('forecastDisplay').addEventListener('change', function () {
            formatForecastDisplay();
            recalculateAllRows();
        });
        document.getElementById('projectPeriod').addEventListener('change', recalculateAllRows);

        function recalculateAllRows() {
            const rows = document.querySelectorAll('#materialTableBody tr');
            rows.forEach(row => {
                const input = row.querySelector('.qty-req');
                if (input) calculateRow(input);
            });

            const cycleRows = document.querySelectorAll('#cycleTimeTableBody tr');
            cycleRows.forEach(row => {
                const input = row.querySelector('.ct-hour') || row.querySelector('.ct-sec');
                if (input) calculateCycleRow(input);
            });
        }
    </script>
@endsection