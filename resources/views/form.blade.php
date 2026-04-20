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

        .material-row-no-header,
        .material-row-no-cell {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .material-row-no-header input,
        .material-row-no-cell input {
            width: 14px;
            height: 14px;
            cursor: pointer;
            accent-color: #2563eb;
        }

        .material-row-number {
            min-width: 1.2rem;
            text-align: right;
            display: inline-block;
        }

        .material-header-filter {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .material-filter-btn {
            width: 18px;
            height: 18px;
            border: 1px solid #cbd5e1;
            background: #fff;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            cursor: pointer;
            padding: 0;
        }

        .material-filter-btn.is-active {
            background: #dbeafe;
            border-color: #60a5fa;
            color: #1d4ed8;
        }

        .material-filter-popup {
            position: fixed;
            z-index: 1200;
            width: 280px;
            max-height: 460px;
            border: 1px solid #94a3b8;
            border-radius: 4px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.2);
            display: none;
            overflow: hidden;
        }

        .material-filter-popup.show {
            display: block;
        }

        .material-filter-popup-head {
            padding: 0.45rem 0.6rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.74rem;
            font-weight: 700;
            color: #1e293b;
            background: #f1f5f9;
        }

        .material-filter-popup-search {
            padding: 0.45rem 0.6rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .material-filter-popup-sort {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.2rem;
            padding: 0.35rem 0.35rem 0.2rem;
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
        }

        .material-filter-popup-sort .btn {
            justify-content: flex-start;
            padding: 0.28rem 0.45rem;
            font-size: 0.74rem;
            border-radius: 3px;
            border: 1px solid transparent;
            background: transparent;
            color: #0f172a;
        }

        .material-filter-popup-sort .btn.is-active {
            background: #dbeafe;
            border-color: #60a5fa;
            color: #1d4ed8;
        }

        .material-filter-popup-sort .btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .material-filter-separator {
            border-top: 1px solid #e2e8f0;
            margin: 0.2rem 0.35rem;
        }

        .material-filter-clear-line {
            padding: 0 0.5rem 0.35rem;
        }

        .material-filter-clear-line .btn {
            width: 100%;
            justify-content: flex-start;
            border-radius: 3px;
            background: transparent;
            border: 1px solid transparent;
            font-size: 0.73rem;
            color: #0f172a;
            padding: 0.28rem 0.45rem;
        }

        .material-filter-clear-line .btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .material-filter-popup-search input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 2px;
            padding: 0.32rem 0.45rem;
            font-size: 0.74rem;
        }

        .material-filter-popup-list {
            max-height: 230px;
            overflow: auto;
            padding: 0.4rem 0.6rem;
            display: flex;
            flex-direction: column;
            gap: 0.22rem;
        }

        .material-filter-popup-item {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.74rem;
            color: #334155;
            line-height: 1.25;
            word-break: break-word;
        }

        .material-filter-popup-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 0.45rem 0.65rem 0.55rem;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .material-filter-popup-actions .btn {
            padding: 0.32rem 0.65rem;
            font-size: 0.73rem;
        }

        /* Highlight import-sensitive pricing columns: Amount 1 .. Import Tax */
        .form-page .material-table thead th:nth-child(8),
        .form-page .material-table thead th:nth-child(9),
        .form-page .material-table thead th:nth-child(10),
        .form-page .material-table thead th:nth-child(11),
        .form-page .material-table thead th:nth-child(12),
        .form-page .material-table thead th:nth-child(13),
        .form-page .material-table thead th:nth-child(14) {
            background: #fff5cc;
        }

        .form-page .material-table tbody td:nth-child(8),
        .form-page .material-table tbody td:nth-child(9),
        .form-page .material-table tbody td:nth-child(10),
        .form-page .material-table tbody td:nth-child(11),
        .form-page .material-table tbody td:nth-child(12),
        .form-page .material-table tbody td:nth-child(13),
        .form-page .material-table tbody td:nth-child(14) {
            background: #fffdf0;
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

        .form-page .form-section-title {
            justify-content: flex-start;
            gap: 0.55rem;
        }

        .form-page .section-actions {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .form-page .section-actions + .section-toggle {
            margin-left: 0.5rem !important;
        }

        .form-page .section-toggle {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border: 1px solid var(--slate-200);
            border-radius: 0.5rem;
            background: #fff;
            color: var(--slate-600);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .form-page .section-toggle:hover {
            border-color: var(--blue-300);
            color: var(--blue-700);
            background: var(--blue-50);
        }

        .form-page .btn + .section-toggle {
            margin-left: 0.5rem;
        }

        .form-page .section-toggle svg {
            width: 16px;
            height: 16px;
            transition: transform 0.2s ease;
        }

        .confirm-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.42);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            z-index: 1000;
        }

        .confirm-modal.is-hidden {
            display: none;
        }

        .confirm-modal-card {
            width: min(460px, 100%);
            background: #fff;
            border: 1px solid var(--slate-200);
            border-radius: 0.95rem;
            box-shadow: 0 24px 52px rgba(15, 23, 42, 0.24);
            overflow: hidden;
        }

        .confirm-modal-head {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.1rem;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            border-bottom: 1px solid var(--slate-200);
        }

        .confirm-modal-icon {
            width: 30px;
            height: 30px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #9a6b00;
            background: #fff7da;
            border: 1px solid #f7d37a;
            flex-shrink: 0;
        }

        .confirm-modal-title {
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--slate-800);
            margin: 0;
        }

        .confirm-modal-body {
            padding: 1rem 1.1rem 1.15rem;
            color: var(--slate-700);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .confirm-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.6rem;
            padding: 0 1.1rem 1.1rem;
        }

        .form-page .form-section.is-collapsed .section-toggle svg {
            transform: rotate(-90deg);
        }

        .form-page .form-section.is-collapsed > :not(.form-section-title) {
            display: none;
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

    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }
    .toast-container .alert {
        pointer-events: auto;
        min-width: 300px;
        max-width: 450px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        margin-bottom: 0;
        opacity: 0;
        transform: translateX(120%);
        animation: toast-slide-in 0.3s ease-out forwards;
        cursor: pointer;
    }
    .toast-container .alert.toast-fadeOut {
        animation: toast-fade-out 0.3s ease-in forwards !important;
    }
    @keyframes toast-slide-in {
        from { transform: translateX(120%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes toast-fade-out {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(120%); opacity: 0; }
    }
</style>

<div class="toast-container" id="mainToastContainer">

    @if(session('success'))
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3l-8.47-14.14a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <line x1="15" y1="9" x2="9" y2="15" />
                <line x1="9" y1="9" x2="15" y2="15" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <line x1="15" y1="9" x2="9" y2="15" />
                <line x1="9" y1="9" x2="15" y2="15" />
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mainToastContainer');
    if (container) {
        const toasts = container.querySelectorAll('.alert');
        toasts.forEach(toast => {
            toast.addEventListener('click', () => {
                toast.classList.add('toast-fadeOut');
                setTimeout(() => toast.remove(), 300);
            });
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    toast.classList.add('toast-fadeOut');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 4000);
        });
    }
});
</script>

<div class="alert alert-warning" id="unpricedTopBanner"
        style="{{ (isset($trackingRevision) && $trackingRevision && isset($openUnpricedParts) && $openUnpricedParts->count() > 0) ? '' : 'display:none;' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3l-8.47-14.14a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
            <span id="unpricedTopBannerText">
                Terdapat {{ isset($openUnpricedParts) ? $openUnpricedParts->count() : 0 }} part yang belum memiliki harga pada versi dokumen ini.
            </span>
    </div>

    <div class="form-page">
    <form action="{{ route('costing.store', absolute: false) }}" method="POST" id="costingForm" enctype="multipart/form-data" autocomplete="off">
        @csrf
        <input type="hidden" name="update_section" id="updateSectionInput" value="">
        @if(isset($costingData) && $costingData)
            <input type="hidden" name="costing_data_id" value="{{ $costingData->id }}">
        @endif
        @if(isset($trackingRevisionId) && $trackingRevisionId)
            <input type="hidden" name="tracking_revision_id" value="{{ $trackingRevisionId }}">
        @endif
        <input type="hidden" id="trackingRevisionId" value="{{ $trackingRevisionId ?? '' }}">
        <input type="hidden" id="updateUnpricedPriceUrl"
            value="{{ isset($trackingRevision) && $trackingRevision ? route('tracking-documents.update-unpriced-price', ['revision' => $trackingRevision->id], absolute: false) : '' }}">
        <input type="hidden" id="deleteUnpricedPartUrl"
            value="{{ isset($trackingRevision) && $trackingRevision ? route('tracking-documents.delete-unpriced-part', ['revision' => $trackingRevision->id], absolute: false) : '' }}">
        <input type="hidden" id="bulkDeleteUnpricedUrl"
            value="{{ isset($trackingRevision) && $trackingRevision ? route('tracking-documents.bulk-delete-unpriced-parts', ['revision' => $trackingRevision->id], absolute: false) : '' }}">

        <!-- Section A: Filter & Header -->
        <div class="card form-section">
            <div class="form-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                </svg>
                Informasi Project
                <div class="section-actions">
                    <button type="submit" class="btn btn-primary btn-sm section-update-btn" name="update_section" value="informasi_project" data-section="informasi_project" formnovalidate>
                        Update
                    </button>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Business Categories</label>
                    <select name="business_category_id" class="form-select" id="productInput" required>
                        @php
                            $selectedBusinessCategoryId = old('business_category_id', $trackingProjectPrefill['business_category_id'] ?? '');
                            if ($selectedBusinessCategoryId === '' && isset($costingData) && $costingData && $costingData->product) {
                                $matchedCategory = $businessCategories->first(function ($category) use ($costingData) {
                                    return trim((string) $category->code) === trim((string) $costingData->product->code)
                                        || trim((string) $category->name) === trim((string) $costingData->product->name);
                                });
                                $selectedBusinessCategoryId = $matchedCategory?->id ?? '';
                            }
                        @endphp
                        <option value="">-- Pilih Business Categories --</option>
                        @foreach($businessCategories as $businessCategory)
                            <option value="{{ $businessCategory->id }}" {{ (string) $selectedBusinessCategoryId === (string) $businessCategory->id ? 'selected' : '' }}>
                                {{ $businessCategory->code }} - {{ $businessCategory->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select" id="customerInput" required>
                        @php
                            $selectedCustomerId = old('customer_id', $costingData->customer_id ?? ($trackingProjectPrefill['customer_id'] ?? ''));
                        @endphp
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (string) $selectedCustomerId === (string) $customer->id ? 'selected' : '' }}>
                                {{ $customer->code }} - {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-input" placeholder="Model"
                        value="{{ old('model', $costingData->model ?? ($trackingProjectPrefill['model'] ?? '')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Assy No.</label>
                    <input type="text" name="assy_no" class="form-input" placeholder="Assy No."
                        value="{{ old('assy_no', $costingData->assy_no ?? ($trackingProjectPrefill['assy_no'] ?? '')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Assy Name</label>
                    <input type="text" name="assy_name" class="form-input" placeholder="Assy Name"
                        value="{{ old('assy_name', $costingData->assy_name ?? ($trackingProjectPrefill['assy_name'] ?? '')) }}">
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
                        @php
                            $selectedPlant = old('line', $costingData->line ?? ($plants->first()?->code ?? ''));
                        @endphp
                        <option value="">-- Pilih Plant --</option>
                        @foreach($plants as $plant)
                            <option value="{{ $plant->code }}" {{ (string) $selectedPlant === (string) $plant->code ? 'selected' : '' }}>
                                {{ $plant->code }} - {{ $plant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group period-group">
                    <label class="form-label">Periode</label>
                    <select name="period" class="form-select" id="periodInput">
                        @php
                            $defaultPeriod = $activeWireRate && $activeWireRate->period_month
                                ? $activeWireRate->period_month->format('Y-m')
                                : '';
                            $selectedPeriod = old('period', $costingData->period ?? $defaultPeriod);
                        @endphp
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periods as $period)
                            @php
                                $periodLabel = preg_match('/^\d{4}-\d{2}$/', (string) $period)
                                    ? \Carbon\Carbon::createFromFormat('Y-m', (string) $period)->translatedFormat('M Y')
                                    : $period;
                            @endphp
                            <option value="{{ $period }}" {{ (string) $selectedPeriod === (string) $period ? 'selected' : '' }}>
                                {{ $periodLabel }}
                            </option>
                        @endforeach
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
                <div class="section-actions">
                    <button type="submit" class="btn btn-primary btn-sm section-update-btn" name="update_section" value="rates" data-section="rates" formnovalidate>
                        Update
                    </button>
                </div>
            </div>
            <div class="form-grid param-grid">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Rate Aktif</label>
                    <select name="wire_rate_id" id="wireRateSelector" class="form-select" onchange="updateRatesFromWireRate(this)">
                        @foreach($wireRates as $wr)
                            @php
                                $wrLabel = $wr->period_month
                                    ? $wr->period_month->format('M-Y')
                                    : ($wr->request_name ?? 'Request #' . $wr->id);
                            @endphp
                            <option value="{{ $wr->id }}"
                                data-usd="{{ $wr->usd_rate }}"
                                data-jpy="{{ $wr->jpy_rate }}"
                                data-lme="{{ $wr->lme_active }}"
                                {{ (int) $selectedWireRateId === (int) $wr->id ? 'selected' : '' }}>
                                {{ $wrLabel }} | JPY: {{ rtrim(rtrim(number_format((float)$wr->jpy_rate, 2, ',', '.'), '0'), ',') }} | USD: {{ number_format((float)$wr->usd_rate, 0, ',', '.') }} | LME: {{ number_format((float)$wr->lme_active, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">USD</label>
                    <input type="number" name="exchange_rate_usd" class="form-input" id="rateUSD"
                        value="{{ $costingData->exchange_rate_usd ?? ($activeWireRate->usd_rate ?? 15500) }}" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">JPY</label>
                    <input type="number" name="exchange_rate_jpy" class="form-input" id="rateJPY"
                        value="{{ $costingData->exchange_rate_jpy ?? ($activeWireRate->jpy_rate ?? 103) }}" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">IDR</label>
                    <input type="number" name="exchange_rate_idr" class="form-input" id="rateIDR" value="1"
                        disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">LME Rate</label>
                    <input type="number" name="lme_rate" class="form-input" id="lmeRate"
                        value="{{ $costingData->lme_rate ?? ($activeWireRate->lme_active ?? '') }}" step="0.01" placeholder="8500">
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
                <div class="section-actions">
                    <button type="submit" class="btn btn-primary btn-sm section-update-btn" name="update_section" value="material" data-section="material" formnovalidate>
                        Update
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="triggerPartlistImport()">
                        Import Partlist
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" id="materialUndoBtn" onclick="undoMaterialTable()" disabled aria-label="Undo" title="Undo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="9 14 4 9 9 4"></polyline>
                            <path d="M20 20a8 8 0 0 0-8-8H4"></path>
                        </svg>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" id="materialRedoBtn" onclick="redoMaterialTable()" disabled aria-label="Redo" title="Redo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="15 14 20 9 15 4"></polyline>
                            <path d="M4 20a8 8 0 0 1 8-8h8"></path>
                        </svg>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" id="materialDeleteSelectedBtn" onclick="deleteSelectedMaterialRows()">
                        Hapus Terpilih
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="addMaterialRow()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Tambah Baris
                    </button>
                </div>
            </div>

            <div class="material-table-container">
                <table class="material-table" id="materialTable">
                    <thead>
                        <tr>
                            <th>
                                <span class="material-row-no-header">
                                    <input type="checkbox" id="materialSelectAllRows" title="Pilih semua baris"
                                        onchange="toggleAllMaterialRowCheckboxes(this.checked)">
                                    <span>No</span>
                                </span>
                            </th>
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
                        @php
                            $oldMaterialRows = (!$costingData && $errors->any()) ? old('materials') : null;
                        @endphp

                        @if(is_array($oldMaterialRows) && count($oldMaterialRows) > 0)
                            @foreach($oldMaterialRows as $index => $row)
                            <tr data-row="{{ $index }}">
                                <td>
                                    <span class="material-row-no-cell">
                                        <input type="checkbox" class="material-row-select" title="Pilih baris">
                                        <span class="material-row-number">{{ $index + 1 }}</span>
                                    </span>
                                </td>
                                <td><input type="text" class="form-input part-no" name="materials[{{ $index }}][part_no]"
                                    value="{{ $row['part_no'] ?? '' }}" placeholder="Part No"></td>
                                <td><input type="text" class="form-input id-code" name="materials[{{ $index }}][id_code]"
                                    value="{{ $row['id_code'] ?? '' }}" placeholder="ID Code"></td>
                                <td><input type="text" class="form-input part-name" name="materials[{{ $index }}][part_name]"
                                    value="{{ $row['part_name'] ?? '' }}" placeholder="Part Name"></td>
                                <td><input type="text" class="form-input w-28 qty-req number-format" name="materials[{{ $index }}][qty_req]" autocomplete="off"
                                    value="{{ number_format((float) ($row['qty_req'] ?? 0), 0, ',', '.') }}" data-original-qty-req="{{ intval($row['qty_req'] ?? 0) }}" onchange="calculateRow(this)"></td>
                                <td><input type="text" class="form-input unit" name="materials[{{ $index }}][unit]"
                                    value="{{ isset($row['unit']) ? strtoupper(trim((string) $row['unit'])) : '' }}" placeholder="Unit"></td>
                                <td><input type="text" class="form-input pro-code" name="materials[{{ $index }}][pro_code]"
                                    value="{{ $row['pro_code'] ?? '' }}" placeholder="Pro Code"></td>
                                <td><input type="text" class="form-input amount1 number-format" name="materials[{{ $index }}][amount1]" autocomplete="off" value="{{ rtrim(rtrim(number_format((float) ($row['amount1'] ?? 0), 4, ',', '.'), '0'), ',') }}" data-original-amount1="{{ $row['amount1'] ?? 0 }}"
                                    step="0.0001" onchange="calculateRow(this)"></td>
                                <td><input type="text" class="form-input unit-price-basis" name="materials[{{ $index }}][unit_price_basis]"
                                    value="{{ $row['unit_price_basis_text'] ?? $row['unit_price_basis'] ?? '' }}" placeholder="Unit Price"
                                    onchange="calculateRow(this)"></td>
                                <td>
                                @php $rowCurrency = $row['currency'] ?? 'IDR'; @endphp
                                <select class="form-select currency" name="materials[{{ $index }}][currency]" onchange="calculateRow(this)">
                                    <option value="IDR" {{ $rowCurrency == 'IDR' ? 'selected' : '' }}>IDR</option>
                                    <option value="USD" {{ $rowCurrency == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="JPY" {{ $rowCurrency == 'JPY' ? 'selected' : '' }}>JPY</option>
                                </select>
                                </td>
                                <td><input type="text" class="form-input w-28 qty-moq number-format" name="materials[{{ $index }}][qty_moq]" value="{{ rtrim(rtrim(number_format((float) ($row['qty_moq'] ?? 0), 6, ',', '.'), '0'), ',') }}" data-original-moq="{{ $row['qty_moq'] ?? 0 }}"
                                    step="0.0001" onchange="calculateRow(this)"></td>
                                <td>
                                @php $rowCn = $row['cn_type'] ?? 'N'; @endphp
                                <select class="form-select cn-type" name="materials[{{ $index }}][cn_type]" onchange="calculateRow(this)">
                                    <option value="N" {{ $rowCn == 'N' ? 'selected' : '' }}>N</option>
                                    <option value="C" {{ $rowCn == 'C' ? 'selected' : '' }}>C</option>
                                </select>
                                </td>
                                <td><input type="text" class="form-input supplier" name="materials[{{ $index }}][supplier]"
                                    value="{{ $row['supplier'] ?? '' }}" placeholder="Supplier"></td>
                                <td><input type="number" class="form-input import-tax" name="materials[{{ $index }}][import_tax]"
                                    value="{{ $row['import_tax'] ?? 0 }}" step="0.01" onchange="calculateRow(this)"></td>
                                <td class="calculated multiply-factor">1.0000</td>
                                <td class="calculated amount2" data-original-amount2="{{ $row['amount2'] ?? 0 }}">0.0000</td>
                                <td class="calculated currency2">{{ $rowCurrency }}</td>
                                <td class="calculated unit-price2">{{ isset($row['unit']) ? strtoupper(trim((string) $row['unit'])) : '' }}</td>
                                <td class="calculated total-price">Rp {{ rtrim(rtrim(number_format((float) ($row['amount1'] ?? 0), 4, ',', '.'), '0'), ',') }}</td>
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
                        @elseif($materialBreakdowns->count() > 0)
                            @foreach($materialBreakdowns as $index => $breakdown)
                                @php
                                    $partNoDisplay = trim((string) ($breakdown->part_no ?? ''));
                                    if ($partNoDisplay === '') {
                                        $partNoDisplay = $breakdown->material->material_code ?? '';
                                        if (str_starts_with((string) $partNoDisplay, '__ROW_') || $partNoDisplay === '__PLACEHOLDER__') {
                                            $partNoDisplay = '-';
                                        }
                                    }
                                    $partNameDisplay = trim((string) ($breakdown->part_name ?? ''));
                                    if ($partNameDisplay === '') {
                                        $partNameDisplay = $breakdown->material->material_description ?? '';
                                    }
                                    $unitDisplay = strtoupper(trim((string) ($breakdown->material?->base_uom ?? '')));
                                @endphp
                                <tr data-row="{{ $index }}">
                                    <td>
                                        <span class="material-row-no-cell">
                                            <input type="checkbox" class="material-row-select" title="Pilih baris">
                                            <span class="material-row-number">{{ $index + 1 }}</span>
                                        </span>
                                    </td>
                                    <td><input type="text" class="form-input part-no" name="materials[{{ $index }}][part_no]"
                                    value="{{ $partNoDisplay }}" placeholder="Part No"></td>
                                    <td><input type="text" class="form-input id-code" name="materials[{{ $index }}][id_code]"
                                    value="{{ $breakdown->id_code ?? '' }}" placeholder="ID Code"></td>
                                    <td><input type="text" class="form-input part-name" name="materials[{{ $index }}][part_name]"
                                    value="{{ $partNameDisplay }}" placeholder="Part Name"></td>
                                            <td><input type="text" class="form-input w-28 qty-req number-format" name="materials[{{ $index }}][qty_req]" autocomplete="off"
                                            value="{{ number_format((float) ($breakdown->qty_req), 0, ',', '.') }}" data-original-qty-req="{{ intval($breakdown->qty_req) }}" onchange="calculateRow(this)"></td>
                                    <td><input type="text" class="form-input unit" name="materials[{{ $index }}][unit]"
                                    value="{{ $unitDisplay }}" placeholder="Unit"></td>
                                    <td><input type="text" class="form-input pro-code" name="materials[{{ $index }}][pro_code]"
                                            value="{{ $breakdown->pro_code ?? '' }}" placeholder="Pro Code"></td>
                                            <td><input type="text" class="form-input amount1 number-format" name="materials[{{ $index }}][amount1]" autocomplete="off" value="{{ rtrim(rtrim(number_format((float) ($breakdown->amount1), 4, ',', '.'), '0'), ',') }}" data-original-amount1="{{ $breakdown->amount1 }}"
                                            step="0.0001" onchange="calculateRow(this)"></td>
                                        <td><input type="text" class="form-input unit-price-basis" name="materials[{{ $index }}][unit_price_basis]"
                                            value="{{ $breakdown->unit_price_basis_text ?? $breakdown->unit_price_basis }}" placeholder="Unit Price"
                                            onchange="calculateRow(this)">
                                    </td>
                                    <td>
                                        <select class="form-select currency" name="materials[{{ $index }}][currency]" onchange="calculateRow(this)">
                                            <option value="IDR" {{ $breakdown->currency == 'IDR' ? 'selected' : '' }}>IDR</option>
                                            <option value="USD" {{ $breakdown->currency == 'USD' ? 'selected' : '' }}>USD</option>
                                            <option value="JPY" {{ $breakdown->currency == 'JPY' ? 'selected' : '' }}>JPY</option>
                                        </select>
                                    </td>
                                        <td><input type="text" class="form-input w-28 qty-moq number-format" name="materials[{{ $index }}][qty_moq]" value="{{ rtrim(rtrim(number_format((float) ($breakdown->qty_moq), 6, ',', '.'), '0'), ',') }}" data-original-moq="{{ $breakdown->qty_moq }}"
                                            step="0.0001" onchange="calculateRow(this)"></td>
                                    <td>
                                        <select class="form-select cn-type" name="materials[{{ $index }}][cn_type]" onchange="calculateRow(this)">
                                            <option value="N" {{ $breakdown->cn_type == 'N' ? 'selected' : '' }}>N</option>
                                            <option value="C" {{ $breakdown->cn_type == 'C' ? 'selected' : '' }}>C</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-input supplier" name="materials[{{ $index }}][supplier]"
                                            value="{{ $breakdown->material->maker ?? '' }}" placeholder="Supplier"></td>
                                    <td><input type="number" class="form-input import-tax" name="materials[{{ $index }}][import_tax]"
                                            value="{{ $breakdown->import_tax_percent }}" step="0.01" onchange="calculateRow(this)">
                                    </td>
                                    <td class="calculated multiply-factor">1.0000</td>
                                    <td class="calculated amount2" data-original-amount2="{{ $breakdown->amount2 ?? 0 }}">{{ rtrim(rtrim(number_format($breakdown->amount2 ?? 0, 4, ',', '.'), '0'), ',') }}</td>
                                    <td class="calculated currency2">{{ $breakdown->currency ?? 'IDR' }}</td>
                                        <td class="calculated unit-price2">{{ isset($breakdown->material?->base_uom) ? strtoupper(trim((string) $breakdown->material->base_uom)) : '' }}</td>
                                    <td class="calculated total-price">Rp {{ rtrim(rtrim(number_format((float) ($breakdown->amount1 ?? 0), 4, ',', '.'), '0'), ',') }}</td>
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
                                    <td>
                                        <span class="material-row-no-cell">
                                            <input type="checkbox" class="material-row-select" title="Pilih baris">
                                            <span class="material-row-number">{{ $i + 1 }}</span>
                                        </span>
                                    </td>
                                    <td><input type="text" class="form-input part-no" name="materials[{{ $i }}][part_no]" value=""
                                            placeholder="Part No"></td>
                                    <td><input type="text" class="form-input id-code" name="materials[{{ $i }}][id_code]" value=""
                                            placeholder="ID Code"></td>
                                    <td><input type="text" class="form-input part-name" name="materials[{{ $i }}][part_name]"
                                            value="" placeholder="Part Name"></td>
                                            <td><input type="text" class="form-input w-28 qty-req number-format" name="materials[{{ $i }}][qty_req]" autocomplete="off"
                                            value="0" data-original-qty-req="0" onchange="calculateRow(this)"></td>
                                    <td><input type="text" class="form-input unit" name="materials[{{ $i }}][unit]" value="PCS"
                                            placeholder="Unit"></td>
                                    <td><input type="text" class="form-input pro-code" name="materials[{{ $i }}][pro_code]" value=""
                                            placeholder="Pro Code"></td>
                                            <td><input type="text" class="form-input amount1 number-format" name="materials[{{ $i }}][amount1]" autocomplete="off" value="0" data-original-amount1="0" step="0.0001"
                                            onchange="calculateRow(this)"></td>
                                    <td><input type="text" class="form-input unit-price-basis" name="materials[{{ $i }}][unit_price_basis]" value="" placeholder="Unit Price"
                                            onchange="calculateRow(this)"></td>
                                    <td>
                                        <select class="form-select currency" name="materials[{{ $i }}][currency]" onchange="calculateRow(this)">
                                            <option value="IDR">IDR</option>
                                            <option value="USD">USD</option>
                                            <option value="JPY">JPY</option>
                                        </select>
                                    </td>
                                        <td><input type="text" class="form-input w-28 qty-moq number-format" name="materials[{{ $i }}][qty_moq]" value="0" data-original-moq="0" step="0.0001"
                                            onchange="calculateRow(this)"></td>
                                    <td>
                                        <select class="form-select cn-type" name="materials[{{ $i }}][cn_type]" onchange="calculateRow(this)">
                                            <option value="N">N</option>
                                            <option value="C">C</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-input supplier" name="materials[{{ $i }}][supplier]" value=""
                                            placeholder="Supplier"></td>
                                        <td><input type="text" class="form-input import-tax number-format" name="materials[{{ $i }}][import_tax]" value="0" step="0.01"
                                            onchange="calculateRow(this)"></td>
                                    <td class="calculated multiply-factor">1.0000</td>
                                    <td class="calculated amount2" data-original-amount2="0">0.0000</td>
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

        <div class="card form-section">
            <div class="form-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3h18v18H3z" />
                    <line x1="3" y1="9" x2="21" y2="9" />
                    <line x1="8" y1="9" x2="8" y2="21" />
                </svg>
                Rekapan Part Tanpa Harga
                <div class="section-actions">
                    <button type="submit" class="btn btn-primary btn-sm section-update-btn" name="update_section" value="unpriced_parts" data-section="unpriced_parts" formnovalidate>
                        Update
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" id="unpricedDeleteSelectedBtn" onclick="deleteSelectedUnpricedRows()">
                        Hapus Terpilih
                    </button>
                    @if(isset($trackingRevision) && $trackingRevision)
                        <a href="{{ route('tracking-documents.export-unpriced', ['revision' => $trackingRevision->id, 'format' => 'excel'], absolute: false) }}"
                            class="btn btn-secondary">Export Unpriced Parts (Excel)</a>
                        <a href="{{ route('tracking-documents.export-unpriced', ['revision' => $trackingRevision->id, 'format' => 'pdf'], absolute: false) }}"
                            target="_blank" class="btn btn-secondary">Export Unpriced Parts (PDF)</a>
                    @endif
                </div>
            </div>

            <div class="material-table-container">
                <table class="material-table">
                    <thead>
                        <tr>
                            <th rowspan="2">
                                <span style="display:inline-flex;align-items:center;gap:0.3rem;">
                                    <input type="checkbox" id="unpricedSelectAll" title="Pilih semua baris"
                                        onchange="toggleAllUnpricedRowCheckboxes(this.checked)">
                                    <span>No</span>
                                </span>
                            </th>
                            <th rowspan="2">Part No</th>
                            <th rowspan="2">ID Code</th>
                            <th rowspan="2">Part Name</th>
                            <th rowspan="2">Qty</th>
                            <th colspan="9">Price</th>
                            <th rowspan="2">Input Harga (Manual)</th>
                            <th rowspan="2">Aksi</th>
                        </tr>
                        <tr>
                            <th>Price</th>
                            <th>Purchase Unit</th>
                            <th>Currency</th>
                            <th>MOQ</th>
                            <th>C/N</th>
                            <th>Maker</th>
                            <th>Add Cost (%)</th>
                            <th>Price Update</th>
                            <th>Price Before</th>
                        </tr>
                    </thead>
                    <tbody id="unpricedRecapBody">
                        @if(isset($openUnpricedParts) && $openUnpricedParts->count() > 0)
                            @foreach($openUnpricedParts as $unpricedIdx => $item)
                                @php
                                    $matchedMaterials = collect($item->matched_materials ?? []);
                                    $matchedWires = collect($item->matched_wires ?? []);
                                    $matchedSource = $item->matched_source ?? null;
                                @endphp
                                <tr data-unpriced-part="{{ $item->part_number }}">
                                    <td>
                                        <span style="display:inline-flex;align-items:center;gap:0.3rem;">
                                            <input type="checkbox" class="unpriced-row-select" data-part-number="{{ $item->part_number }}">
                                            <span>{{ $unpricedIdx + 1 }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <div>{{ $item->part_number }}</div>
                                        @if(!empty($item->matched_material_description))
                                            <div style="font-size: 0.8rem; color: var(--slate-500); margin-top: 0.25rem;">
                                                {{ $item->matched_material_description }}
                                                @if($matchedSource === 'wire')
                                                    <span style="background: #dbeafe; color: #1e40af; padding: 0.1rem 0.3rem; border-radius: 0.25rem; font-size: 0.7rem; margin-left: 0.3rem;">WIRE</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($matchedMaterials->isNotEmpty())
                                            @foreach($matchedMaterials as $matched)
                                                <div>{{ $matched->material_code ?: '-' }}</div>
                                            @endforeach
                                        @elseif($matchedSource === 'wire' && !empty($item->matched_wire_idcode))
                                            {{ $item->matched_wire_idcode }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->part_name ?: '-' }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>
                                        @if($matchedMaterials->isNotEmpty())
                                            @foreach($matchedMaterials as $matched)
                                                @php
                                                    $matchedPrice = (float) ($matched->price ?? 0);
                                                    $selectedDetectedPrice = (float) ($item->detected_price ?? 0);
                                                    $isMatchedChecked = $selectedDetectedPrice > 0 && abs($matchedPrice - $selectedDetectedPrice) < 0.0001;
                                                @endphp
                                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                                    <input type="checkbox"
                                                        class="matched-price-select"
                                                        data-part-number="{{ $item->part_number }}"
                                                        data-price="{{ $matchedPrice }}"
                                                        data-currency="{{ $matched->currency ?? '' }}"
                                                        data-unit="{{ $matched->purchase_unit ?? '' }}"
                                                        data-moq="{{ $matched->moq ?? 0 }}"
                                                        data-cn="{{ $matched->cn ?? 'N' }}"
                                                        data-supplier="{{ $matched->maker ?? '' }}"
                                                        data-import-tax="{{ $matched->add_cost_import_tax ?? 0 }}"
                                                        {{ $isMatchedChecked ? 'checked' : '' }}>
                                                    <span>{{ rtrim(rtrim(number_format($matchedPrice, 4, ',', '.'), '0'), ',') }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            @if($matchedSource === 'wire' && isset($item->matched_price))
                                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                                    <input type="checkbox"
                                                        class="matched-price-select"
                                                        data-part-number="{{ $item->part_number }}"
                                                        data-price="{{ (float) $item->matched_price }}"
                                                        data-currency="{{ $item->matched_currency ?? 'IDR' }}"
                                                        data-unit="{{ $matchedSource === 'wire' ? 'm' : ($item->matched_purchase_unit ?? '') }}"
                                                        data-moq="{{ $item->matched_moq ?? 0 }}"
                                                        data-cn="{{ $item->matched_cn ?? 'N' }}"
                                                        data-supplier="{{ $item->matched_maker ?? '' }}"
                                                        data-import-tax="{{ $item->matched_add_cost_import_tax ?? 0 }}"
                                                        checked>
                                                    <span>{{ rtrim(rtrim(number_format((float) $item->matched_price, 4, ',', '.'), '0'), ',') }}</span>
                                                </div>
                                            @else
                                                {{ isset($item->matched_price) && $item->matched_price !== null ? rtrim(rtrim(number_format((float) $item->matched_price, 4, ',', '.'), '0'), ',') : rtrim(rtrim(number_format((float) ($item->detected_price ?? 0), 4, ',', '.'), '0'), ',') }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($matchedMaterials->isNotEmpty())
                                            @foreach($matchedMaterials as $matched)
                                                <div>{{ $matched->purchase_unit ?: '-' }}</div>
                                            @endforeach
                                        @elseif($matchedSource === 'wire')
                                            m
                                        @else
                                            {{ $item->matched_purchase_unit ?: '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($matchedMaterials->isNotEmpty())
                                            @foreach($matchedMaterials as $matched)
                                                <div>{{ $matched->currency ?: '-' }}</div>
                                            @endforeach
                                        @else
                                            {{ $item->matched_currency ?: '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($matchedMaterials->isNotEmpty())
                                            @foreach($matchedMaterials as $matched)
                                                <div>{{ isset($matched->moq) && $matched->moq !== null ? rtrim(rtrim(number_format((float) $matched->moq, 2, ',', '.'), '0'), ',') : '-' }}</div>
                                            @endforeach
                                        @else
                                            {{ isset($item->matched_moq) && $item->matched_moq !== null ? rtrim(rtrim(number_format((float) $item->matched_moq, 2, ',', '.'), '0'), ',') : '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($matchedMaterials->isNotEmpty())
                                            @foreach($matchedMaterials as $matched)
                                                <div>{{ $matched->cn ?: '-' }}</div>
                                            @endforeach
                                        @else
                                            {{ $item->matched_cn ?: '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($matchedMaterials->isNotEmpty())
                                            @foreach($matchedMaterials as $matched)
                                                <div>{{ $matched->maker ?: '-' }}</div>
                                            @endforeach
                                        @else
                                            {{ $item->matched_maker ?: '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($matchedMaterials->isNotEmpty())
                                            @foreach($matchedMaterials as $matched)
                                                <div>{{ isset($matched->add_cost_import_tax) && $matched->add_cost_import_tax !== null ? rtrim(rtrim(number_format((float) $matched->add_cost_import_tax, 2, ',', '.'), '0'), ',') : '-' }}</div>
                                            @endforeach
                                        @else
                                            {{ isset($item->matched_add_cost_import_tax) && $item->matched_add_cost_import_tax !== null ? rtrim(rtrim(number_format((float) $item->matched_add_cost_import_tax, 2, ',', '.'), '0'), ',') : '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($matchedMaterials->isNotEmpty())
                                            @foreach($matchedMaterials as $matched)
                                                <div>{{ !empty($matched->price_update) ? \Illuminate\Support\Carbon::parse($matched->price_update)->format('Y-m-d') : '-' }}</div>
                                            @endforeach
                                        @else
                                            {{ !empty($item->matched_price_update) ? \Illuminate\Support\Carbon::parse($item->matched_price_update)->format('Y-m-d') : '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($matchedMaterials->isNotEmpty())
                                            @foreach($matchedMaterials as $matched)
                                                <div>{{ isset($matched->price_before) && $matched->price_before !== null ? rtrim(rtrim(number_format((float) $matched->price_before, 2, ',', '.'), '0'), ',') : '-' }}</div>
                                            @endforeach
                                        @else
                                            {{ isset($item->matched_price_before) && $item->matched_price_before !== null ? rtrim(rtrim(number_format((float) $item->matched_price_before, 2, ',', '.'), '0'), ',') : '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" class="form-input unpriced-manual-price number-format"
                                            name="manual_unpriced_prices[{{ $item->part_number }}]"
                                            data-part-number="{{ $item->part_number }}"
                                            value="{{ $item->manual_price ?? '' }}" placeholder="Isi harga jika sudah ada">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm unpriced-add-price-btn" data-part-number="{{ $item->part_number }}">
                                            Tambah
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm unpriced-delete-btn" data-part-number="{{ $item->part_number }}">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="16" style="text-align: center; color: var(--slate-500);">
                                    Belum ada part tanpa harga untuk versi dokumen ini.
                                </td>
                            </tr>
                        @endif
                    </tbody>
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
                <div class="section-actions">
                    <button type="submit" class="btn btn-primary btn-sm section-update-btn" name="update_section" value="cycle_time" data-section="cycle_time" formnovalidate>
                        Update
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="addCycleTimeRow()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Tambah Baris
                    </button>
                </div>
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

        <!-- Section C: Resume COGM -->
        <div class="card form-section">
            <div class="form-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 1v22" />
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>
                Resume COGM
                <div class="section-actions">
                    <button type="submit" class="btn btn-primary btn-sm section-update-btn" name="update_section" value="resume_cogm" data-section="resume_cogm" formnovalidate>
                        Update
                    </button>
                </div>
            </div>
            <div class="form-grid cost-grid">
                <div class="form-group">
                    <label class="form-label">Total Material Cost (IDR)</label>
                    <input type="number" name="material_cost" class="form-input" id="materialCost"
                        value="{{ $costingData->material_cost ?? '' }}" required placeholder="0"
                        readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Process Cost (IDR)</label>
                    <input type="number" name="labor_cost" class="form-input" id="laborCost"
                        value="{{ $costingData->labor_cost ?? '' }}" required placeholder="0"
                        readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Depresiasi Tooling Cost (IDR)</label>
                    <input type="number" name="overhead_cost" class="form-input" id="overheadCost"
                        value="{{ $costingData->overhead_cost ?? '' }}" placeholder="0"
                        onchange="calculateTotals()">
                </div>
                <div class="form-group">
                    <label class="form-label">Administrasi Cost (IDR)</label>
                    <input type="number" name="scrap_cost" class="form-input" id="scrapCost"
                        value="{{ $costingData->scrap_cost ?? '' }}" placeholder="0"
                        onchange="calculateTotals()">
                </div>
            </div>

            <input type="hidden" name="revenue" id="revenue" value="{{ $costingData->revenue ?? 0 }}">
            <input type="hidden" name="qty_good" id="qtyGood" value="{{ $costingData->qty_good ?? 0 }}">

            <div class="calc-box" style="margin-top: 1.5rem;">
                <div class="calc-item">
                    <span class="calc-label">Total Material Cost</span>
                    <span class="calc-value" id="calcTotalMaterialCost">Rp 0</span>
                </div>
                <div class="calc-item">
                    <span class="calc-label">Process Cost</span>
                    <span class="calc-value" id="calcProcessCost">Rp 0</span>
                </div>
                <div class="calc-item">
                    <span class="calc-label">Depresiasi Tooling Cost</span>
                    <span class="calc-value" id="calcToolingCost">Rp 0</span>
                </div>
                <div class="calc-item">
                    <span class="calc-label">Administrasi Cost</span>
                    <span class="calc-value" id="calcAdministrasiCost">Rp 0</span>
                </div>
                <div class="calc-item">
                    <span class="calc-label">COGM</span>
                    <span class="calc-value" id="calcCogsTotal">Rp 0</span>
                </div>
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

    <form action="{{ route('costing.import-partlist', absolute: false) }}" method="POST" id="partlistImportForm" enctype="multipart/form-data" style="position:absolute; width:0; height:0; overflow:hidden;">
        @csrf
        @if(isset($costingData) && $costingData)
            <input type="hidden" name="costing_data_id" value="{{ $costingData->id }}">
        @endif
        @if(isset($trackingRevisionId) && $trackingRevisionId)
            <input type="hidden" name="tracking_revision_id" value="{{ $trackingRevisionId }}">
        @endif
        <input type="hidden" name="wire_rate_id" id="importWireRateId" value="{{ $selectedWireRateId }}">
        <input type="hidden" name="business_category_id" id="importBusinessCategoryId" value="{{ $costingData->product->line ?? ($trackingProjectPrefill['business_category_id'] ?? '') }}">
        <input type="hidden" name="customer_id" id="importCustomerId" value="{{ $costingData->customer_id ?? ($trackingProjectPrefill['customer_id'] ?? '') }}">
        <input type="hidden" name="period" id="importPeriod" value="{{ $costingData->period ?? '' }}">
        <input type="hidden" name="line" id="importLine" value="{{ $costingData->line ?? '' }}">
        <input type="hidden" name="model" id="importModel" value="{{ $costingData->model ?? ($trackingProjectPrefill['model'] ?? '') }}">
        <input type="hidden" name="assy_no" id="importAssyNo" value="{{ $costingData->assy_no ?? ($trackingProjectPrefill['assy_no'] ?? '') }}">
        <input type="hidden" name="assy_name" id="importAssyName" value="{{ $costingData->assy_name ?? ($trackingProjectPrefill['assy_name'] ?? '') }}">
        <input type="hidden" name="exchange_rate_usd" id="importRateUsd" value="{{ $costingData->exchange_rate_usd ?? ($activeWireRate->usd_rate ?? 15500) }}">
        <input type="hidden" name="exchange_rate_jpy" id="importRateJpy" value="{{ $costingData->exchange_rate_jpy ?? ($activeWireRate->jpy_rate ?? 103) }}">
        <input type="hidden" name="lme_rate" id="importLmeRate" value="{{ $costingData->lme_rate ?? ($activeWireRate->lme_active ?? '') }}">
        <input type="hidden" name="forecast" id="importForecast" value="{{ $forecastValue ?? 0 }}">
        <input type="hidden" name="project_period" id="importProjectPeriod" value="{{ $costingData->project_period ?? 2 }}">
        <input type="file" name="import_partlist_file" id="importPartlistFileInput" accept=".xls,.xlsx" onchange="if(this.files && this.files.length){ submitPartlistImport(); }">
    </form>

    <div id="partlistImportConfirmModal" class="confirm-modal is-hidden" aria-hidden="true">
        <div class="confirm-modal-card" role="dialog" aria-modal="true" aria-labelledby="partlistImportConfirmTitle">
            <div class="confirm-modal-head">
                <span class="confirm-modal-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3l-8.47-14.14a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                </span>
                <h3 id="partlistImportConfirmTitle" class="confirm-modal-title">Konfirmasi Update Partlist</h3>
            </div>
            <div class="confirm-modal-body">
                Yakin ingin mengupdate partlist? Data material yang ada akan digantikan dari file partlist.
            </div>
            <div class="confirm-modal-actions">
                <button type="button" class="btn btn-secondary" id="partlistImportCancelBtn">Batal</button>
                <button type="button" class="btn btn-primary" id="partlistImportOkBtn">Ya, Update Partlist</button>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Global variables
        let rowCounter = {{ (!$costingData && is_array(old('materials')) && count(old('materials')) > 0) ? count(old('materials')) : ($materialBreakdowns->count() > 0 ? $materialBreakdowns->count() : 5) }};
        let cycleRowCounter = {{ $initialCycleCount }};
        let materialUndoHistory = [];
        let materialRedoHistory = [];
        const materialUndoLimit = 50;
        let materialHistoryApplying = false;
        const materialFilterState = {};
        const materialFilterableColumns = [1, 2, 3, 5, 6, 9, 11, 12];
        let materialFilterPopup = null;
        let activeMaterialFilterColumn = null;
        let materialSortState = { column: null, direction: null };

        // Materials data for dynamic selection (slim: only fields needed for JS lookup)
        const materials = @json($materialsSlim);
        const materialMasterByCode = new Map();
        materials.forEach((item) => {
            const codeKey = String(item?.material_code || '').trim().toUpperCase();
            if (codeKey !== '') {
                materialMasterByCode.set(codeKey, item);
            }
        });
        const cycleProcessOptions = @json(($cycleTimeTemplates ?? collect())->pluck('process')->values());
        const hasServerUnpricedData = {{ (isset($openUnpricedParts) && $openUnpricedParts->count() > 0) ? 'true' : 'false' }};
        const unpricedSyncTimers = {};

        // Format number as Rupiah
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }

        
        // Auto-format numbers with dots for thousands and comma for decimal
        
        // Convert JS float (e.g. 4000.25) to input format '4.000,25'
        function floatToInput(num) {
            let str = String(Number(num));
            str = str.replace('.', ',');
            return formatNumberInput(str);
        }

        function formatNumberInput(value) {
            if (!value) return '';
            let raw = value.toString().replace(/[^0-9,]/g, '');
            let parts = raw.split(',');
            let integerPart = parts[0];
            let decimalPart = parts.length > 1 ? ',' + parts[1] : '';
            
            // Add thousand separators
            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            return integerPart + decimalPart;
        }

        document.addEventListener('input', function(e) {
            if (e.target && e.target.classList.contains('number-format')) {
                let startPos = e.target.selectionStart;
                let oldVal = e.target.value;
                
                // Allow user to type comma if it's the last character
                if (oldVal.endsWith(',') && (oldVal.match(/,/g) || []).length === 1) {
                    return;
                }

                let newVal = formatNumberInput(oldVal);
                e.target.value = newVal;
                
                // adjust cursor
                if (startPos !== null) {
                    let diff = newVal.length - oldVal.length;
                    let newPos = startPos + diff;
                    // basic heuristic, might jump to end on some browsers but works fine
                    e.target.setSelectionRange(newPos, newPos);
                }
            }
        });
        
        document.addEventListener('blur', function(e) {
            if (e.target && e.target.classList.contains('number-format')) {
                // remove trailing commas on blur
                let v = e.target.value;
                if (v.endsWith(',')) {
                    e.target.value = v.slice(0, -1);
                }
                
                // also calculateRow if not already fired by browser
                if (typeof calculateRow === 'function' && e.target.closest('tr')) {
                    calculateRow(e.target);
                }
            }
        }, true);

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

            str = str.replace(/\s+/g, '');
            str = str.replace(/[^0-9,\.\-]/g, '');

            if (str === '' || str === '-' || str === '.' || str === ',') {
                return 0;
            }

            const hasComma = str.includes(',');
            const hasDot = str.includes('.');

            if (hasComma && hasDot) {
                const lastCommaPos = str.lastIndexOf(',');
                const lastDotPos = str.lastIndexOf('.');

                if (lastCommaPos > lastDotPos) {
                    str = str.replace(/\./g, '');
                    str = str.replace(/,/g, '.');
                } else {
                    str = str.replace(/,/g, '');
                }
            } else if (hasComma && !hasDot) {
                str = str.replace(/,/g, '.');
            }

            return parseFloat(str) || 0;
        }

        function findMaterialMasterForRow(row) {
            if (!row) return null;

            const partNo = String(row.querySelector('.part-no')?.value || '').trim().toUpperCase();
            const idCode = String(row.querySelector('.id-code')?.value || '').trim().toUpperCase();

            if (partNo && materialMasterByCode.has(partNo)) {
                return materialMasterByCode.get(partNo);
            }

            if (idCode && materialMasterByCode.has(idCode)) {
                return materialMasterByCode.get(idCode);
            }

            return null;
        }

        function applyMasterMaterialToRow(row) {
            const master = findMaterialMasterForRow(row);
            if (!master || !row) {
                return false;
            }

            const partNameInput = row.querySelector('.part-name');
            const unitInput = row.querySelector('.unit');
            const supplierInput = row.querySelector('.supplier');
            const amount1Input = row.querySelector('.amount1');
            const qtyMoqInput = row.querySelector('.qty-moq');
            const importTaxInput = row.querySelector('.import-tax');
            const currencySelect = row.querySelector('.currency');
            const cnTypeSelect = row.querySelector('.cn-type');

            if (partNameInput && String(partNameInput.value || '').trim() === '') {
                partNameInput.value = String(master.material_description || '').toUpperCase();
            }

            if (unitInput && String(unitInput.value || '').trim() === '') {
                unitInput.value = String(master.base_uom || 'PCS').toUpperCase();
            }

            if (supplierInput && String(supplierInput.value || '').trim() === '') {
                supplierInput.value = String(master.maker || '').toUpperCase();
            }

            if (currencySelect && String(currencySelect.value || '').trim() === '') {
                const currency = String(master.currency || 'IDR').toUpperCase();
                if (['IDR', 'USD', 'JPY'].includes(currency)) {
                    currencySelect.value = currency;
                }
            }

            if (cnTypeSelect) {
                const current = String(cnTypeSelect.value || '').toUpperCase();
                if (current !== 'C' && current !== 'N') {
                    const fromMaster = String(master.cn || 'N').toUpperCase();
                    cnTypeSelect.value = fromMaster === 'C' ? 'C' : 'N';
                }
            }

            if (qtyMoqInput) {
                const currentMoq = parseInputNumber(qtyMoqInput.value || 0);
                const masterMoq = Number(master.moq || 0);
                if (currentMoq <= 0 && masterMoq > 0) {
                    qtyMoqInput.value = floatToInput(masterMoq);
                }
            }

            if (importTaxInput && String(importTaxInput.value || '').trim() === '' && master.add_cost_import_tax !== null && master.add_cost_import_tax !== undefined) {
                importTaxInput.value = floatToInput(master.add_cost_import_tax || 0);
            }

            if (amount1Input) {
                const currentAmount1 = parseInputNumber(amount1Input.value || 0);
                const masterPrice = Number(master.price || 0);
                if (currentAmount1 <= 0 && masterPrice > 0) {
                    amount1Input.value = floatToInput(masterPrice);
                }
            }

            return true;
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

            // Total Price (IDR) = Qty Req * Amount 2 * Exchange Rate.
            const total = qty * amount2 * exchangeRate;

            const totalPriceElement = row.querySelector('.total-price');
            totalPriceElement.textContent = formatRupiah(total);
            totalPriceElement.setAttribute('data-value', total);

            // Recalculate all totals
            calculateTableTotal();
            refreshUnpricedRecap();
        }

        // Calculate table total
        function calculateTableTotal(syncMaterialCost = true) {
            let total = 0;
            const rows = document.querySelectorAll('#materialTableBody tr');

            rows.forEach(row => {
                const totalElement = row.querySelector('.total-price');
                const dataValue = totalElement ? (parseInputNumber(totalElement.getAttribute('data-value')) || 0) : 0;
                total += dataValue;
            });

            // Update Footer Total using the rendered totals so it stays aligned with Database Costing
            const materialCostInput = document.getElementById('materialCost');
            if (materialCostInput && syncMaterialCost) {
                materialCostInput.value = total;
                calculateTotals(false);
            }

            document.getElementById('tableTotalMaterial').textContent = formatRupiah(total);

            return total;
        }

        function refreshUnpricedRecap() {
            const tbody = document.getElementById('unpricedRecapBody');
            if (!tbody) return;

            // Only show server-persisted unpriced data.
            // The recap is populated when the user clicks "Update" in the
            // Rekapan Part Tanpa Harga section, which triggers server-side
            // processing and returns the data via $openUnpricedParts.
            if (hasServerUnpricedData) {
                const visibleRows = tbody.querySelectorAll('tr[data-unpriced-part]').length;
                const banner = document.getElementById('unpricedTopBanner');
                const bannerText = document.getElementById('unpricedTopBannerText');

                if (banner) {
                    banner.style.display = visibleRows > 0 ? 'flex' : 'none';
                }
                if (bannerText && visibleRows > 0) {
                    bannerText.textContent = `Terdapat ${visibleRows} part yang belum memiliki harga pada versi dokumen ini.`;
                }

                bindUnpricedManualPriceInputs();
                bindUnpricedDeleteButtons();
                bindMatchedPriceSelectors();
                bindUnpricedAddPriceButtons();
                return;
            }

            // No server data — show empty message
            tbody.innerHTML = '<tr><td colspan="16" style="text-align: center; color: var(--slate-500);">Klik tombol "Update" di section ini untuk mendeteksi part tanpa harga.</td></tr>';
            const banner = document.getElementById('unpricedTopBanner');
            if (banner) banner.style.display = 'none';
        }

        function bindMatchedPriceSelectors() {
            const selectors = document.querySelectorAll('.matched-price-select');
            selectors.forEach((selector) => {
                if (!(selector instanceof HTMLInputElement)) {
                    return;
                }

                if (selector.dataset.boundMatchedPrice === '1') {
                    return;
                }

                selector.dataset.boundMatchedPrice = '1';

                selector.addEventListener('change', function () {
                    const partNumber = this.dataset.partNumber || '';
                    if (!partNumber) {
                        return;
                    }

                    const escapedPart = (typeof CSS !== 'undefined' && typeof CSS.escape === 'function')
                        ? CSS.escape(partNumber)
                        : partNumber.replace(/([\\[\\]\\.\\:\\#\"'])/g, '\\\\$1');

                    const siblingSelectors = document.querySelectorAll(`.matched-price-select[data-part-number="${escapedPart}"]`);

                    if (this.checked) {
                        siblingSelectors.forEach((sibling) => {
                            if (sibling !== this && sibling instanceof HTMLInputElement) {
                                sibling.checked = false;
                            }
                        });
                    }
                });
            });
        }

        function bindUnpricedAddPriceButtons() {
            const buttons = document.querySelectorAll('.unpriced-add-price-btn');
            buttons.forEach((button) => {
                if (!(button instanceof HTMLButtonElement)) {
                    return;
                }

                if (button.dataset.boundAddPrice === '1') {
                    return;
                }

                button.dataset.boundAddPrice = '1';

                button.addEventListener('click', function () {
                    const partNumber = this.dataset.partNumber || '';
                    if (!partNumber) {
                        return;
                    }

                    const row = this.closest('tr');
                    if (!(row instanceof HTMLTableRowElement)) {
                        return;
                    }

                    const selectedOption = row.querySelector('.matched-price-select:checked');
                    if (!(selectedOption instanceof HTMLInputElement)) {
                        window.alert('Pilih salah satu harga terlebih dahulu.');
                        return;
                    }

                    const selectedPrice = parseFloat(selectedOption.dataset.price || '0') || 0;
                    const selectedCurrency = selectedOption.dataset.currency || '';
                    const selectedUnit = selectedOption.dataset.unit || '';
                    const selectedMoq = parseFloat(selectedOption.dataset.moq || '0') || 0;
                    const selectedCn = selectedOption.dataset.cn || 'N';
                    const selectedSupplier = selectedOption.dataset.supplier || '';
                    const selectedImportTax = parseFloat(selectedOption.dataset.importTax || '0') || 0;

                    applySelectedMatchedPrice(partNumber, selectedPrice, selectedCurrency, selectedUnit, selectedMoq, selectedCn, selectedSupplier, selectedImportTax);
                });
            });
        }

        function normalizePartKey(value) {
            return String(value || '').trim().toLowerCase();
        }

        function applySelectedMatchedPrice(partNumber, selectedPrice, selectedCurrency, selectedUnit, selectedMoq, selectedCn, selectedSupplier, selectedImportTax) {
            const escapedPart = (typeof CSS !== 'undefined' && typeof CSS.escape === 'function')
                ? CSS.escape(partNumber)
                : partNumber.replace(/([\\[\\]\\.\\:\\#\"'])/g, '\\\\$1');

            const manualInput = document.querySelector(`#unpricedRecapBody .unpriced-manual-price[data-part-number="${escapedPart}"]`);
            if (manualInput instanceof HTMLInputElement) {
                manualInput.value = selectedPrice > 0 ? floatToInput(selectedPrice) : '';
            }

            const targetKey = normalizePartKey(partNumber);
            let updatedRows = 0;

            document.querySelectorAll('#materialTableBody tr').forEach((row) => {
                const partInput = row.querySelector('.part-no');
                const amountInput = row.querySelector('.amount1');
                const currencySelect = row.querySelector('.currency');
                const unitInput = row.querySelector('.unit-price-basis');
                const moqInput = row.querySelector('.qty-moq');
                const cnSelect = row.querySelector('.cn-type');
                const supplierInput = row.querySelector('.supplier');
                const importTaxInput = row.querySelector('.import-tax');

                if (!(partInput instanceof HTMLInputElement) || !(amountInput instanceof HTMLInputElement)) {
                    return;
                }

                if (normalizePartKey(partInput.value) !== targetKey) {
                    return;
                }

                amountInput.value = selectedPrice > 0 ? floatToInput(selectedPrice) : '0';

                if (currencySelect instanceof HTMLSelectElement && selectedCurrency) {
                    const hasOption = Array.from(currencySelect.options).some((opt) => opt.value === selectedCurrency);
                    if (hasOption) {
                        currencySelect.value = selectedCurrency;
                    }
                }
                
                if (unitInput && selectedUnit) {
                    unitInput.value = selectedUnit;
                }
                if (moqInput) {
                    moqInput.value = floatToInput(selectedMoq);
                }
                if (cnSelect instanceof HTMLSelectElement && selectedCn) {
                    const hasOption = Array.from(cnSelect.options).some((opt) => opt.value === selectedCn);
                    if (hasOption) {
                        cnSelect.value = selectedCn;
                    }
                }
                if (supplierInput) {
                    supplierInput.value = selectedSupplier || '';
                }
                if (importTaxInput) {
                    importTaxInput.value = floatToInput(selectedImportTax);
                }

                calculateRow(amountInput);
                updatedRows += 1;
            });

            calculateTableTotal();
            syncManualPriceToServer(partNumber, selectedPrice);

            if (updatedRows > 0) {
                submitMaterialSection();
            }
        }

        function bindUnpricedManualPriceInputs() {
            const inputs = document.querySelectorAll('.unpriced-manual-price');
            inputs.forEach((input) => {
                if (input.dataset.boundRealtime === '1') {
                    return;
                }

                input.dataset.boundRealtime = '1';
                input.addEventListener('input', function () {
                    const partNumber = this.dataset.partNumber || '';
                    if (!partNumber) return;

                    if (unpricedSyncTimers[partNumber]) {
                        clearTimeout(unpricedSyncTimers[partNumber]);
                    }

                    unpricedSyncTimers[partNumber] = setTimeout(() => {
                        syncManualPriceToServer(partNumber, this.value);
                    }, 450);
                });
            });
        }

        function bindUnpricedDeleteButtons() {
            const buttons = document.querySelectorAll('.unpriced-delete-btn');
            buttons.forEach((button) => {
                if (button.dataset.boundDelete === '1') {
                    return;
                }

                button.dataset.boundDelete = '1';
                button.addEventListener('click', function () {
                    const partNumber = this.dataset.partNumber || '';
                    if (!partNumber) return;

                    openAppConfirm(`Hapus part tanpa harga "${partNumber}"?`, function() {
                        removeUnpricedRow(partNumber);
                    });
                });
            });

            document.querySelectorAll('#unpricedRecapBody .unpriced-row-select').forEach(cb => {
                if (cb.dataset.boundChange === '1') return;
                cb.dataset.boundChange = '1';
                cb.addEventListener('change', updateUnpricedSelectAllState);
            });
        }

        function removeUnpricedRow(partNumber) {
            const row = document.querySelector(`#unpricedRecapBody tr[data-unpriced-part="${CSS.escape(partNumber)}"]`);
            if (row) {
                row.remove();
            }
            renumberUnpricedRows();
            updateUnpricedSelectAllState();

            // Also sync to server if URL available
            deleteUnpricedPartFromServer(partNumber);
        }

        function deleteUnpricedPartFromServer(partNumber) {
            const trackingRevisionId = document.getElementById('trackingRevisionId')?.value || '';
            const url = document.getElementById('deleteUnpricedPartUrl')?.value || '';
            if (!trackingRevisionId || !url) return Promise.resolve();

            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ part_number: partNumber })
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.ok) {
                    updateUnpricedBanner(data.open_unpriced_count || 0);
                }
            })
            .catch(() => {});
        }

        function updateUnpricedBanner(openCount) {
            const banner = document.getElementById('unpricedTopBanner');
            const bannerText = document.getElementById('unpricedTopBannerText');
            if (banner) banner.style.display = openCount > 0 ? 'flex' : 'none';
            if (bannerText) bannerText.textContent = `Terdapat ${openCount} part yang belum memiliki harga pada versi dokumen ini.`;
        }

        function toggleAllUnpricedRowCheckboxes(checked) {
            document.querySelectorAll('#unpricedRecapBody .unpriced-row-select').forEach(cb => {
                cb.checked = checked;
            });
        }

        function updateUnpricedSelectAllState() {
            const all = document.querySelectorAll('#unpricedRecapBody .unpriced-row-select');
            const checked = document.querySelectorAll('#unpricedRecapBody .unpriced-row-select:checked');
            const selectAll = document.getElementById('unpricedSelectAll');
            if (selectAll) {
                selectAll.checked = all.length > 0 && all.length === checked.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
            }
        }

        function renumberUnpricedRows() {
            const rows = document.querySelectorAll('#unpricedRecapBody tr[data-unpriced-part]');
            rows.forEach((row, idx) => {
                const numSpan = row.querySelector('.unpriced-row-select')?.parentElement?.querySelector('span');
                if (numSpan) numSpan.textContent = idx + 1;
            });
            if (rows.length === 0) {
                const tbody = document.getElementById('unpricedRecapBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="16" style="text-align: center; color: var(--slate-500);">Belum ada part tanpa harga untuk versi dokumen ini.</td></tr>';
                }
            }
        }

        function deleteSelectedUnpricedRows() {
            const selectedRows = Array.from(document.querySelectorAll('#unpricedRecapBody .unpriced-row-select:checked'))
                .map(cb => cb.closest('tr'))
                .filter(row => row instanceof HTMLTableRowElement);

            if (selectedRows.length === 0) return;

            openAppConfirm(
                `Hapus ${selectedRows.length} baris yang dipilih?`,
                function() {
                    showAppLoading('Menghapus...');
                    const partNumbers = selectedRows
                        .map(row => row.dataset.unpricedPart || '')
                        .filter(p => p !== '');

                    // Optimistically remove rows from DOM immediately
                    selectedRows.forEach(row => row.remove());
                    renumberUnpricedRows();
                    updateUnpricedSelectAllState();

                    const bulkUrl = document.getElementById('bulkDeleteUnpricedUrl')?.value || '';
                    const deleteUrl = document.getElementById('deleteUnpricedPartUrl')?.value || '';

                    if (bulkUrl && partNumbers.length > 0) {
                        // Single bulk request
                        fetch(bulkUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ part_numbers: partNumbers })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.ok) {
                                updateUnpricedBanner(data.open_unpriced_count || 0);
                            }
                            hideAppLoading();
                        })
                        .catch(() => {
                            hideAppLoading();
                        });
                    } else if (deleteUrl && partNumbers.length > 0) {
                        // Fallback: single delete for each (old endpoint)
                        Promise.all(partNumbers.map(pn =>
                            fetch(deleteUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ part_number: pn })
                            }).catch(() => {})
                        )).then(() => hideAppLoading());
                    } else {
                        hideAppLoading();
                    }
                }
            );
        }

        function syncManualPriceToServer(partNumber, value) {
            const trackingRevisionId = document.getElementById('trackingRevisionId')?.value || '';
            const url = document.getElementById('updateUnpricedPriceUrl')?.value || '';

            if (!trackingRevisionId || !url) {
                return;
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    part_number: partNumber,
                    manual_price: value === '' ? null : Number(value)
                })
            })
                .then((response) => response.json())
                .then((data) => {
                    if (!data || data.ok !== true) {
                        return;
                    }

                    const banner = document.getElementById('unpricedTopBanner');
                    const bannerText = document.getElementById('unpricedTopBannerText');
                    const openCount = Number(data.open_unpriced_count || 0);

                    if (banner) {
                        banner.style.display = openCount > 0 ? 'flex' : 'none';
                    }

                    if (bannerText) {
                        bannerText.textContent = `Terdapat ${openCount} part yang belum memiliki harga pada versi dokumen ini.`;
                    }
                })
                .catch(() => {
                    // Silent fail: user can still save form as fallback.
                });
        }

        // deleteUnpricedPart replaced by removeUnpricedRow + deleteUnpricedPartFromServer



        // Calculate totals for Resume COGM
        function calculateTotals(recalculateMaterialTable = true) {
            const materialCost = parseFloat(document.getElementById('materialCost').value) || 0;
            const laborCost = parseFloat(document.getElementById('laborCost').value) || 0;
            const overheadCost = parseFloat(document.getElementById('overheadCost').value) || 0;
            const scrapCost = parseFloat(document.getElementById('scrapCost').value) || 0;
            const cogmTotal = materialCost + laborCost + overheadCost + scrapCost;

            document.getElementById('calcTotalMaterialCost').textContent = formatRupiah(materialCost);
            document.getElementById('calcProcessCost').textContent = formatRupiah(laborCost);
            document.getElementById('calcToolingCost').textContent = formatRupiah(overheadCost);
            document.getElementById('calcAdministrasiCost').textContent = formatRupiah(scrapCost);
            document.getElementById('calcCogsTotal').textContent = formatRupiah(cogmTotal);

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
            const beforeSnapshot = getMaterialStateSnapshot();
            const tbody = document.getElementById('materialTableBody');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-row', rowCounter);

            newRow.innerHTML = `
                                    <td><span class="material-row-no-cell"><input type="checkbox" class="material-row-select" title="Pilih baris"><span class="material-row-number">${rowCounter + 1}</span></span></td>
                                    <td><input type="text" class="form-input part-no" name="materials[${rowCounter}][part_no]" value="" placeholder="Part No"></td>
                                    <td><input type="text" class="form-input id-code" name="materials[${rowCounter}][id_code]" value="" placeholder="ID Code"></td>
                                    <td><input type="text" class="form-input part-name" name="materials[${rowCounter}][part_name]" value="" placeholder="Part Name"></td>
                                    <td><input type="text" class="form-input w-28 qty-req number-format" name="materials[${rowCounter}][qty_req]" value="0" step="1" min="0" onchange="calculateRow(this)"></td>
                                    <td><input type="text" class="form-input unit" name="materials[${rowCounter}][unit]" value="PCS" placeholder="Unit"></td>
                                    <td><input type="text" class="form-input pro-code" name="materials[${rowCounter}][pro_code]" value="" placeholder="Pro Code"></td>
                                    <td><input type="text" class="form-input amount1 number-format" name="materials[${rowCounter}][amount1]" value="0" step="0.0001" onchange="calculateRow(this)"></td>
                                    <td><input type="text" class="form-input unit-price-basis" name="materials[${rowCounter}][unit_price_basis]" value="" placeholder="Unit Price" onchange="calculateRow(this)"></td>
                                    <td><select class="form-select currency" name="materials[${rowCounter}][currency]" onchange="calculateRow(this)"><option value="IDR">IDR</option><option value="USD">USD</option><option value="JPY">JPY</option></select></td>
                                    <td><input type="text" class="form-input w-28 qty-moq number-format" name="materials[${rowCounter}][qty_moq]" value="0" step="0.0001" onchange="calculateRow(this)"></td>
                                    <td><select class="form-select cn-type" name="materials[${rowCounter}][cn_type]" onchange="calculateRow(this)"><option value="N">N</option><option value="C">C</option></select></td>
                                    <td><input type="text" class="form-input supplier" name="materials[${rowCounter}][supplier]" value="" placeholder="Supplier"></td>
                                    <td><input type="text" class="form-input import-tax number-format" name="materials[${rowCounter}][import_tax]" value="0" step="0.01" onchange="calculateRow(this)"></td>
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

            const afterSnapshot = getMaterialStateSnapshot();
            pushMaterialHistoryAction({
                type: 'snapshot',
                before: beforeSnapshot,
                after: afterSnapshot,
            });
            applyMaterialFilters();
            updateMaterialSelectAllRowsState();
        }

        // Remove row
        function removeRow(button) {
            const beforeSnapshot = getMaterialStateSnapshot();
            const row = button.closest('tr');
            row.remove();
            renumberRows();
            calculateTableTotal();
            refreshUnpricedRecap();

            const afterSnapshot = getMaterialStateSnapshot();
            pushMaterialHistoryAction({
                type: 'snapshot',
                before: beforeSnapshot,
                after: afterSnapshot,
            });
            applyMaterialFilters();
        }

        // Renumber rows
        function renumberRows() {
            const rows = document.querySelectorAll('#materialTableBody tr');
            rows.forEach((row, index) => {
                const numberEl = row.querySelector('.material-row-number');
                if (numberEl) {
                    numberEl.textContent = String(index + 1);
                } else if (row.cells[0]) {
                    row.cells[0].textContent = index + 1;
                }
            });

            updateMaterialSelectAllRowsState();
        }

        function updateMaterialSelectAllRowsState() {
            const master = document.getElementById('materialSelectAllRows');
            if (!(master instanceof HTMLInputElement)) {
                return;
            }

            const rowCheckboxes = Array.from(document.querySelectorAll('#materialTableBody .material-row-select'));
            if (rowCheckboxes.length === 0) {
                master.checked = false;
                master.indeterminate = false;
                return;
            }

            const checkedCount = rowCheckboxes.filter((cb) => cb instanceof HTMLInputElement && cb.checked).length;
            master.checked = checkedCount === rowCheckboxes.length;
            master.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
        }

        function deleteSelectedMaterialRows() {
            const selectedRows = Array.from(document.querySelectorAll('#materialTableBody .material-row-select:checked'))
                .map((cb) => cb.closest('tr'))
                .filter((row) => row instanceof HTMLTableRowElement);

            if (selectedRows.length === 0) {
                return;
            }

            openAppConfirm(
                'Hapus ' + selectedRows.length + ' baris yang dipilih?',
                function () {
                    const beforeSnapshot = getMaterialStateSnapshot();
                    selectedRows.forEach((row) => row.remove());

                    renumberRows();
                    calculateTableTotal();
                    refreshUnpricedRecap();

                    const afterSnapshot = getMaterialStateSnapshot();
                    pushMaterialHistoryAction({
                        type: 'snapshot',
                        before: beforeSnapshot,
                        after: afterSnapshot,
                    });

                    applyMaterialFilters();
                    updateMaterialSelectAllRowsState();

                    // Auto-save to persist deletion via AJAX, then reload
                    submitMaterialSectionAjax();
                }
            );
        }

        function submitMaterialSectionAjax() {
            const form = document.getElementById('costingForm');
            if (!form) return;

            showAppLoading('Menyimpan perubahan...');

            const formData = new FormData(form);
            formData.set('update_section', 'material');

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(function (resp) {
                // Server returns 302 redirect — any 2xx/3xx is success
                if (resp.ok || resp.status === 302 || resp.redirected) {
                    window.location.reload();
                } else {
                    return resp.text().then(function (txt) {
                        hideAppLoading();
                        openAppNotify('Gagal menyimpan: ' + (resp.status));
                    });
                }
            })
            .catch(function () {
                // Network error — data was likely saved, just reload
                window.location.reload();
            });
        }

        function submitMaterialSection() {
            const form = document.getElementById('costingForm');
            const materialUpdateBtn = document.querySelector('.section-update-btn[data-section="material"]');
            if (!form || !materialUpdateBtn) {
                return;
            }

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit(materialUpdateBtn);
                return;
            }

            materialUpdateBtn.click();
        }

        function getMaterialStateSnapshot() {
            const tbody = document.getElementById('materialTableBody');
            if (!tbody) {
                return null;
            }

            const clone = tbody.cloneNode(true);
            const liveControls = Array.from(tbody.querySelectorAll('input, select, textarea'));
            const cloneControls = Array.from(clone.querySelectorAll('input, select, textarea'));

            cloneControls.forEach((control, index) => {
                const liveControl = liveControls[index];
                if (!liveControl) {
                    return;
                }

                if (control instanceof HTMLInputElement) {
                    control.value = liveControl.value;
                    if (control.type === 'checkbox' || control.type === 'radio') {
                        control.checked = liveControl.checked;
                    }
                } else if (control instanceof HTMLSelectElement) {
                    control.value = liveControl.value;
                    Array.from(control.options).forEach((option) => {
                        option.selected = option.value === liveControl.value;
                    });
                } else if (control instanceof HTMLTextAreaElement) {
                    control.value = liveControl.value;
                    control.textContent = liveControl.value;
                }
            });

            return {
                html: clone.innerHTML,
                rowCounter,
            };
        }

        function updateMaterialUndoButtonState() {
            const undoBtn = document.getElementById('materialUndoBtn');
            const redoBtn = document.getElementById('materialRedoBtn');
            if (!undoBtn) return;
            undoBtn.disabled = materialUndoHistory.length === 0;
            if (redoBtn) {
                redoBtn.disabled = materialRedoHistory.length === 0;
            }
        }

        function pushMaterialHistoryAction(action) {
            if (!action || materialHistoryApplying) {
                return;
            }

            materialUndoHistory.push(action);
            if (materialUndoHistory.length > materialUndoLimit) {
                materialUndoHistory.shift();
            }

            materialRedoHistory = [];

            updateMaterialUndoButtonState();
        }

        function commitActiveMaterialFieldChange() {
            const active = document.activeElement;
            if (!(active instanceof HTMLElement)) {
                return;
            }

            if (!active.matches('#materialTableBody input.form-input, #materialTableBody select.form-select')) {
                return;
            }

            const previousValue = active.dataset.undoValue ?? '';
            const currentValue = active.value ?? '';
            if (previousValue === currentValue) {
                return;
            }

            pushMaterialHistoryAction({
                type: 'field',
                name: active.name,
                oldValue: previousValue,
                newValue: currentValue,
            });

            active.dataset.undoValue = currentValue;
        }

        function applyMaterialFieldValueByName(name, value) {
            if (!name) {
                return;
            }

            const escapedName = (typeof CSS !== 'undefined' && typeof CSS.escape === 'function')
                ? CSS.escape(name)
                : name.replace(/([\[\]\.\:\#])/g, '\\$1');

            const target = document.querySelector(`#materialTableBody [name="${escapedName}"]`);
            if (!(target instanceof HTMLElement)) {
                return;
            }

            target.value = value ?? '';

            if (target instanceof HTMLInputElement && target.type === 'text') {
                target.value = String(target.value || '').toUpperCase();
            }

            target.dataset.undoValue = target.value ?? '';

            recalculateAllRows();
            refreshUnpricedRecap();

            const focused = document.activeElement;
            if (focused instanceof HTMLElement && focused.matches('#materialTableBody input.form-input, #materialTableBody select.form-select')) {
                focused.dataset.undoValue = focused.value ?? '';
            }
        }

        function markMaterialControlsUndoBase() {
            const controls = document.querySelectorAll('#materialTableBody input.form-input, #materialTableBody select.form-select');
            controls.forEach((control) => {
                control.dataset.undoValue = control.value ?? '';
            });
        }

        function applyMaterialAction(action, direction) {
            if (!action) {
                return;
            }

            materialHistoryApplying = true;

            if (action.type === 'field') {
                const targetValue = direction === 'undo' ? action.oldValue : action.newValue;
                applyMaterialFieldValueByName(action.name, targetValue);
            } else if (action.type === 'snapshot') {
                const snapshot = direction === 'undo' ? action.before : action.after;
                restoreMaterialSnapshot(snapshot);
            }

            materialHistoryApplying = false;

            markMaterialControlsUndoBase();

            updateMaterialUndoButtonState();
        }

        function restoreMaterialSnapshot(snapshot) {
            if (!snapshot) {
                return;
            }

            const tbody = document.getElementById('materialTableBody');
            if (!tbody) {
                return;
            }

            tbody.innerHTML = snapshot.html;
            rowCounter = snapshot.rowCounter;

            renumberRows();
            normalizeMaterialTextInputs();
            recalculateAllRows();
            refreshUnpricedRecap();
            bindUnpricedManualPriceInputs();
            bindUnpricedDeleteButtons();
            bindMatchedPriceSelectors();
            bindUnpricedAddPriceButtons();
            applyMaterialFilters();
        }

        function getMaterialRowFilterValue(row, columnIndex) {
            const cell = row.cells[columnIndex];
            if (!cell) return '';

            const control = cell.querySelector('input, select, textarea');
            if (control) {
                return String(control.value ?? '').trim();
            }

            return String(cell.textContent ?? '').trim();
        }

        function getMaterialColumnValues(columnIndex) {
            const rows = Array.from(document.querySelectorAll('#materialTableBody tr'));
            const values = new Set();

            rows.forEach((row) => {
                const value = getMaterialRowFilterValue(row, columnIndex);
                values.add(value === '' ? '(Blanks)' : value);
            });

            return Array.from(values).sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' }));
        }

        function applyMaterialFilters() {
            const rows = Array.from(document.querySelectorAll('#materialTableBody tr'));

            rows.forEach((row) => {
                let visible = true;

                for (const [columnKey, selectedValues] of Object.entries(materialFilterState)) {
                    if (!(selectedValues instanceof Set) || selectedValues.size === 0) {
                        continue;
                    }

                    const columnIndex = Number(columnKey);
                    const rawValue = getMaterialRowFilterValue(row, columnIndex);
                    const normalizedValue = rawValue === '' ? '(Blanks)' : rawValue;

                    if (!selectedValues.has(normalizedValue)) {
                        visible = false;
                        break;
                    }
                }

                row.style.display = visible ? '' : 'none';
            });

            if (materialSortState.column !== null && materialSortState.direction) {
                const columnIndex = Number(materialSortState.column);
                const direction = materialSortState.direction === 'desc' ? -1 : 1;
                const tbody = document.getElementById('materialTableBody');

                if (tbody) {
                    rows.sort((a, b) => {
                        const va = getMaterialRowFilterValue(a, columnIndex);
                        const vb = getMaterialRowFilterValue(b, columnIndex);

                        const na = Number(va);
                        const nb = Number(vb);
                        const bothNumeric = !Number.isNaN(na) && !Number.isNaN(nb) && va !== '' && vb !== '';

                        if (bothNumeric) {
                            if (na === nb) return 0;
                            return (na < nb ? -1 : 1) * direction;
                        }

                        return va.localeCompare(vb, undefined, { sensitivity: 'base', numeric: true }) * direction;
                    });

                    rows.forEach((row) => tbody.appendChild(row));
                    renumberRows();
                }
            }

            updateMaterialFilterButtonsState();
        }

        function updateMaterialFilterButtonsState() {
            const table = document.getElementById('materialTable');
            if (!table) return;

            table.querySelectorAll('.material-filter-btn').forEach((btn) => {
                const col = Number(btn.dataset.col || -1);
                const activeSet = materialFilterState[col];
                const hasFilter = activeSet instanceof Set && activeSet.size > 0;
                const hasSort = materialSortState.column === col && !!materialSortState.direction;
                btn.classList.toggle('is-active', hasFilter || hasSort);
            });
        }

        function setMaterialSort(columnIndex, direction) {
            if (materialSortState.column === columnIndex && materialSortState.direction === direction) {
                materialSortState = { column: null, direction: null };
            } else {
                materialSortState = { column: columnIndex, direction };
            }

            applyMaterialFilters();

            if (activeMaterialFilterColumn !== null) {
                const sortAscBtn = materialFilterPopup?.querySelector('.material-filter-sort-asc');
                const sortDescBtn = materialFilterPopup?.querySelector('.material-filter-sort-desc');
                const ascActive = materialSortState.column === activeMaterialFilterColumn && materialSortState.direction === 'asc';
                const descActive = materialSortState.column === activeMaterialFilterColumn && materialSortState.direction === 'desc';
                sortAscBtn?.classList.toggle('is-active', ascActive);
                sortDescBtn?.classList.toggle('is-active', descActive);
            }
        }

        function closeMaterialFilterPopup() {
            if (!materialFilterPopup) return;
            materialFilterPopup.classList.remove('show');
            activeMaterialFilterColumn = null;
        }

        function renderMaterialFilterOptions(columnIndex, keyword = '') {
            if (!materialFilterPopup) return;

            const list = materialFilterPopup.querySelector('.material-filter-popup-list');
            if (!list) return;

            const selected = materialFilterState[columnIndex] instanceof Set
                ? new Set(materialFilterState[columnIndex])
                : null;
            const values = getMaterialColumnValues(columnIndex).filter((v) => v.toLowerCase().includes(keyword.toLowerCase()));

            list.innerHTML = '';

            const selectAllItem = document.createElement('label');
            selectAllItem.className = 'material-filter-popup-item';
            const selectAllCheckbox = document.createElement('input');
            selectAllCheckbox.type = 'checkbox';
            selectAllCheckbox.className = 'material-filter-select-all-checkbox';
            selectAllCheckbox.checked = values.length > 0 && values.every((v) => !selected || selected.has(v));
            const selectAllText = document.createElement('span');
            selectAllText.textContent = '(Select All)';
            selectAllItem.appendChild(selectAllCheckbox);
            selectAllItem.appendChild(selectAllText);
            list.appendChild(selectAllItem);

            selectAllCheckbox.addEventListener('change', function () {
                const checked = this.checked;
                list.querySelectorAll('.material-filter-value-checkbox').forEach((cb) => {
                    cb.checked = checked;
                });
            });

            values.forEach((value) => {
                const item = document.createElement('label');
                item.className = 'material-filter-popup-item';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'material-filter-value-checkbox';
                checkbox.value = value;
                checkbox.checked = !selected || selected.has(value);

                const text = document.createElement('span');
                text.textContent = value;

                item.appendChild(checkbox);
                item.appendChild(text);
                list.appendChild(item);
            });
        }

        function openMaterialFilterPopup(columnIndex, title, anchorElement) {
            if (!materialFilterPopup || !anchorElement) return;

            activeMaterialFilterColumn = columnIndex;
            materialFilterPopup.querySelector('.material-filter-popup-head').textContent = `Filter: ${title}`;

            const searchInput = materialFilterPopup.querySelector('.material-filter-search-input');
            if (searchInput) {
                searchInput.value = '';
            }

            const sortAscBtn = materialFilterPopup.querySelector('.material-filter-sort-asc');
            const sortDescBtn = materialFilterPopup.querySelector('.material-filter-sort-desc');
            const clearLineBtn = materialFilterPopup.querySelector('.material-filter-clear-line-btn');
            const ascActive = materialSortState.column === columnIndex && materialSortState.direction === 'asc';
            const descActive = materialSortState.column === columnIndex && materialSortState.direction === 'desc';
            sortAscBtn?.classList.toggle('is-active', ascActive);
            sortDescBtn?.classList.toggle('is-active', descActive);
            if (clearLineBtn) {
                clearLineBtn.textContent = `Clear Filter From "${title.toUpperCase()}"`;
            }

            renderMaterialFilterOptions(columnIndex, '');

            const rect = anchorElement.getBoundingClientRect();
            materialFilterPopup.style.top = `${Math.min(window.innerHeight - 380, rect.bottom + 8)}px`;
            materialFilterPopup.style.left = `${Math.max(8, Math.min(window.innerWidth - 280, rect.left))}px`;
            materialFilterPopup.classList.add('show');
        }

        function initMaterialFilterPopup() {
            if (materialFilterPopup) return;

            materialFilterPopup = document.createElement('div');
            materialFilterPopup.className = 'material-filter-popup';
            materialFilterPopup.innerHTML = `
                <div class="material-filter-popup-head">Filter</div>
                <div class="material-filter-popup-sort">
                    <button type="button" class="btn btn-secondary btn-sm material-filter-sort-asc">Sort A to Z</button>
                    <button type="button" class="btn btn-secondary btn-sm material-filter-sort-desc">Sort Z to A</button>
                </div>
                <div class="material-filter-separator"></div>
                <div class="material-filter-clear-line">
                    <button type="button" class="btn btn-secondary btn-sm material-filter-clear-line-btn">Clear Filter</button>
                </div>
                <div class="material-filter-popup-search">
                    <input type="text" class="material-filter-search-input" placeholder="Search...">
                </div>
                <div class="material-filter-popup-list"></div>
                <div class="material-filter-popup-actions">
                    <button type="button" class="btn btn-secondary btn-sm material-filter-cancel-btn">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm material-filter-apply-btn">OK</button>
                </div>
            `;

            document.body.appendChild(materialFilterPopup);

            const searchInput = materialFilterPopup.querySelector('.material-filter-search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    if (activeMaterialFilterColumn === null) return;
                    renderMaterialFilterOptions(activeMaterialFilterColumn, this.value || '');
                });
            }

            const sortAscBtn = materialFilterPopup.querySelector('.material-filter-sort-asc');
            sortAscBtn?.addEventListener('click', function () {
                if (activeMaterialFilterColumn === null) return;
                setMaterialSort(activeMaterialFilterColumn, 'asc');
            });

            const sortDescBtn = materialFilterPopup.querySelector('.material-filter-sort-desc');
            sortDescBtn?.addEventListener('click', function () {
                if (activeMaterialFilterColumn === null) return;
                setMaterialSort(activeMaterialFilterColumn, 'desc');
            });

            const clearLineBtn = materialFilterPopup.querySelector('.material-filter-clear-line-btn');
            clearLineBtn?.addEventListener('click', function () {
                if (activeMaterialFilterColumn === null) return;
                delete materialFilterState[activeMaterialFilterColumn];
                applyMaterialFilters();
                closeMaterialFilterPopup();
            });

            const cancelBtn = materialFilterPopup.querySelector('.material-filter-cancel-btn');
            cancelBtn?.addEventListener('click', function () {
                closeMaterialFilterPopup();
            });

            const applyBtn = materialFilterPopup.querySelector('.material-filter-apply-btn');
            applyBtn?.addEventListener('click', function () {
                if (activeMaterialFilterColumn === null) return;

                const checked = Array.from(materialFilterPopup.querySelectorAll('.material-filter-popup-list .material-filter-value-checkbox:checked'))
                    .map((el) => el.value);

                const allValues = getMaterialColumnValues(activeMaterialFilterColumn);
                if (checked.length === 0 || checked.length === allValues.length) {
                    delete materialFilterState[activeMaterialFilterColumn];
                } else {
                    materialFilterState[activeMaterialFilterColumn] = new Set(checked);
                }

                applyMaterialFilters();
                closeMaterialFilterPopup();
            });

            document.addEventListener('click', function (event) {
                if (!materialFilterPopup || !materialFilterPopup.classList.contains('show')) return;
                const target = event.target;
                if (!(target instanceof Node)) return;

                const clickedFilterBtn = target instanceof Element && target.closest('.material-filter-btn');
                if (materialFilterPopup.contains(target) || clickedFilterBtn) {
                    return;
                }

                closeMaterialFilterPopup();
            });
        }

        function initMaterialHeaderFilters() {
            const table = document.getElementById('materialTable');
            if (!table) return;

            const headerCells = table.querySelectorAll('thead th');
            headerCells.forEach((th, index) => {
                if (!materialFilterableColumns.includes(index)) {
                    return;
                }

                if (th.dataset.filterReady === '1') {
                    return;
                }

                const title = (th.textContent || '').trim();
                th.dataset.filterReady = '1';
                th.dataset.filterTitle = title;

                const wrap = document.createElement('span');
                wrap.className = 'material-header-filter';

                const label = document.createElement('span');
                label.textContent = title;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'material-filter-btn';
                btn.dataset.col = String(index);
                btn.title = `Filter ${title}`;
                btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 4 21 4 14 12 14 19 10 21 10 12 3 4"></polygon></svg>';

                btn.addEventListener('click', function (event) {
                    event.stopPropagation();
                    if (materialFilterPopup?.classList.contains('show') && activeMaterialFilterColumn === index) {
                        closeMaterialFilterPopup();
                        return;
                    }
                    openMaterialFilterPopup(index, title, btn);
                });

                th.textContent = '';
                wrap.appendChild(label);
                wrap.appendChild(btn);
                th.appendChild(wrap);
            });

            updateMaterialFilterButtonsState();
        }

        function undoMaterialTable() {
            // Capture pending active-cell edit first, then revert exactly one action.
            commitActiveMaterialFieldChange();

            if (materialUndoHistory.length === 0) {
                return;
            }

            const action = materialUndoHistory.pop();
            if (!action) {
                updateMaterialUndoButtonState();
                return;
            }

            applyMaterialAction(action, 'undo');

            materialRedoHistory.push(action);
            if (materialRedoHistory.length > materialUndoLimit) {
                materialRedoHistory.shift();
            }

            updateMaterialUndoButtonState();
        }

        function redoMaterialTable() {
            if (materialRedoHistory.length === 0) {
                return;
            }

            const next = materialRedoHistory.pop();
            if (!next) {
                updateMaterialUndoButtonState();
                return;
            }

            applyMaterialAction(next, 'redo');

            materialUndoHistory.push(next);
            if (materialUndoHistory.length > materialUndoLimit) {
                materialUndoHistory.shift();
            }

            updateMaterialUndoButtonState();
        }

        function normalizeMaterialTextInputs(scope = document) {
            const textInputs = scope.querySelectorAll('#materialTableBody input[type="text"]');
            textInputs.forEach((input) => {
                const value = String(input.value || '');
                const upper = value.toUpperCase();
                if (upper !== value) {
                    input.value = upper;
                }
            });
        }

        function moveMaterialFocusByArrow(currentElement, key) {
            const currentRow = currentElement.closest('tr');
            if (!currentRow) return;

            const rows = Array.from(document.querySelectorAll('#materialTableBody tr'));
            const currentRowIndex = rows.indexOf(currentRow);
            if (currentRowIndex < 0) return;

            const getEditableCells = (row) => Array.from(row.querySelectorAll('input.form-input, select.form-select'));
            const currentCells = getEditableCells(currentRow);
            const currentCellIndex = currentCells.indexOf(currentElement);
            if (currentCellIndex < 0) return;

            let nextRowIndex = currentRowIndex;
            let nextCellIndex = currentCellIndex;

            if (key === 'ArrowLeft') nextCellIndex -= 1;
            if (key === 'ArrowRight') nextCellIndex += 1;
            if (key === 'ArrowUp') nextRowIndex -= 1;
            if (key === 'ArrowDown') nextRowIndex += 1;

            if (key === 'ArrowLeft' || key === 'ArrowRight') {
                if (nextCellIndex < 0 || nextCellIndex >= currentCells.length) {
                    return;
                }

                const target = currentCells[nextCellIndex];
                if (!target) return;

                target.focus();
                if (target.tagName === 'INPUT') {
                    target.select();
                }
                return;
            }

            if (nextRowIndex < 0 || nextRowIndex >= rows.length) {
                return;
            }

            const nextRow = rows[nextRowIndex];
            const nextRowCells = getEditableCells(nextRow);
            if (!nextRowCells.length) return;

            const target = nextRowCells[Math.min(currentCellIndex, nextRowCells.length - 1)];
            if (!target) return;

            target.focus();
            if (target.tagName === 'INPUT') {
                target.select();
            }
        }

        function moveMaterialFocusLinear(currentElement, step) {
            const currentRow = currentElement.closest('tr');
            if (!currentRow) return;

            const rows = Array.from(document.querySelectorAll('#materialTableBody tr'));
            const currentRowIndex = rows.indexOf(currentRow);
            if (currentRowIndex < 0) return;

            const getEditableCells = (row) => Array.from(row.querySelectorAll('input.form-input, select.form-select'));
            const currentCells = getEditableCells(currentRow);
            const currentCellIndex = currentCells.indexOf(currentElement);
            if (currentCellIndex < 0) return;

            let nextRowIndex = currentRowIndex;
            let nextCellIndex = currentCellIndex + step;

            if (nextCellIndex >= currentCells.length) {
                nextRowIndex += 1;
                if (nextRowIndex >= rows.length) {
                    return;
                }
                nextCellIndex = 0;
            } else if (nextCellIndex < 0) {
                nextRowIndex -= 1;
                if (nextRowIndex < 0) {
                    return;
                }
                const prevCells = getEditableCells(rows[nextRowIndex]);
                nextCellIndex = Math.max(prevCells.length - 1, 0);
            }

            const nextCells = getEditableCells(rows[nextRowIndex]);
            if (!nextCells.length) return;

            const target = nextCells[Math.min(nextCellIndex, nextCells.length - 1)];
            if (!target) return;

            target.focus();
            if (target.tagName === 'INPUT') {
                target.select();
            }
        }

        function bindMaterialTableBehaviors() {
            const materialBody = document.getElementById('materialTableBody');
            if (!materialBody || materialBody.dataset.boundBehavior === '1') {
                return;
            }

            materialBody.dataset.boundBehavior = '1';

            materialBody.addEventListener('input', function (event) {
                const target = event.target;
                if (!(target instanceof HTMLInputElement)) {
                    return;
                }

                if (target.type !== 'text') {
                    return;
                }

                const currentValue = String(target.value || '');
                const upperValue = currentValue.toUpperCase();
                if (upperValue === currentValue) {
                    return;
                }

                const start = target.selectionStart;
                const end = target.selectionEnd;
                target.value = upperValue;
                if (start !== null && end !== null) {
                    target.setSelectionRange(start, end);
                }
            });

            materialBody.addEventListener('focusin', function (event) {
                const target = event.target;
                if (!(target instanceof HTMLElement)) {
                    return;
                }

                if (!target.matches('input.form-input, select.form-select')) {
                    return;
                }

                target.dataset.undoValue = target.value ?? '';
            });

            materialBody.addEventListener('change', function (event) {
                const target = event.target;
                if (!(target instanceof HTMLElement)) {
                    return;
                }

                if (target.matches('.part-no, .id-code')) {
                    const row = target.closest('tr');
                    if (row instanceof HTMLTableRowElement) {
                        const updated = applyMasterMaterialToRow(row);
                        if (updated) {
                            const input = row.querySelector('.qty-req') || row.querySelector('.amount1') || row.querySelector('.unit-price-basis');
                            if (input) {
                                calculateRow(input);
                            }
                        }
                    }
                }

                if (target.matches('.material-row-select')) {
                    updateMaterialSelectAllRowsState();
                    return;
                }

                if (!target.matches('input.form-input, select.form-select')) {
                    return;
                }

                const previousValue = target.dataset.undoValue ?? '';
                const currentValue = target.value ?? '';
                if (previousValue === currentValue) {
                    return;
                }

                pushMaterialHistoryAction({
                    type: 'field',
                    name: target.name,
                    oldValue: previousValue,
                    newValue: currentValue,
                });

                target.dataset.undoValue = currentValue;
                applyMaterialFilters();
            });

            materialBody.addEventListener('keydown', function (event) {
                const target = event.target;
                if (!(target instanceof HTMLElement)) {
                    return;
                }

                if (!target.matches('input.form-input, select.form-select')) {
                    return;
                }

                if (event.key === 'Enter') {
                    event.preventDefault();
                    moveMaterialFocusLinear(target, event.shiftKey ? -1 : 1);
                    return;
                }

                if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(event.key)) {
                    return;
                }

                event.preventDefault();
                moveMaterialFocusByArrow(target, event.key);
            });

            const masterSelectAll = document.getElementById('materialSelectAllRows');
            if (masterSelectAll && masterSelectAll.dataset.boundSelectAll !== '1') {
                masterSelectAll.dataset.boundSelectAll = '1';
                masterSelectAll.addEventListener('change', function () {
                    const checked = !!this.checked;
                    document.querySelectorAll('#materialTableBody .material-row-select').forEach((cb) => {
                        if (cb instanceof HTMLInputElement) {
                            cb.checked = checked;
                        }
                    });
                    updateMaterialSelectAllRowsState();
                });
            }
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

            // Sync process cost in Resume COGM from total cycle time cost
            const laborCostInput = document.getElementById('laborCost');
            if (laborCostInput) {
                laborCostInput.value = Math.round(totalCostUnit);
            }

            calculateTotals(false);
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

        function updateRatesFromWireRate(select) {
            const option = select.options[select.selectedIndex];
            if (!option) return;
            const usd = option.getAttribute('data-usd');
            const jpy = option.getAttribute('data-jpy');
            const lme = option.getAttribute('data-lme');
            if (usd !== null) document.getElementById('rateUSD').value = usd;
            if (jpy !== null) document.getElementById('rateJPY').value = jpy;
            if (lme !== null) document.getElementById('lmeRate').value = lme;
        }

        function toggleAllMaterialRowCheckboxes(checked) {
            document.querySelectorAll('#materialTableBody .material-row-select').forEach(function (cb) {
                if (cb instanceof HTMLInputElement) {
                    cb.checked = checked;
                }
            });
        }

        function initSectionToggles() {
            const sections = document.querySelectorAll('.form-page .form-section');

            sections.forEach((section, index) => {
                const title = section.querySelector('.form-section-title');
                if (!title) return;

                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.className = 'section-toggle';
                toggleBtn.setAttribute('aria-expanded', 'true');
                toggleBtn.setAttribute('aria-controls', `section-content-${index}`);
                toggleBtn.title = 'Hide/Show bagian ini';
                toggleBtn.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                `;

                toggleBtn.addEventListener('click', () => {
                    const isCollapsed = section.classList.toggle('is-collapsed');
                    toggleBtn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
                });

                title.appendChild(toggleBtn);
            });
        }

        function shouldKeepFieldForSection(fieldName, section) {
            if (!fieldName) return false;

            const alwaysKeep = [
                '_token',
                'costing_data_id',
                'tracking_revision_id',
                'update_section',
                'import_partlist',
                'import_partlist_file'
            ];

            if (alwaysKeep.includes(fieldName)) {
                return true;
            }

            const sectionExactFields = {
                informasi_project: ['business_category_id', 'customer_id', 'period', 'line', 'model', 'assy_no', 'assy_name', 'forecast', 'project_period'],
                rates: ['exchange_rate_usd', 'exchange_rate_jpy', 'lme_rate'],
                material: ['forecast', 'project_period', 'material_cost', 'labor_cost', 'overhead_cost', 'scrap_cost', 'revenue', 'qty_good', 'import_partlist'],
                unpriced_parts: ['tracking_revision_id'],
                cycle_time: ['cycle_times'],
                resume_cogm: ['material_cost', 'labor_cost', 'overhead_cost', 'scrap_cost', 'revenue', 'qty_good']
            };

            const sectionPrefixes = {
                material: ['materials[', 'manual_unpriced_prices['],
                unpriced_parts: ['materials[', 'manual_unpriced_prices['],
                cycle_time: ['cycle_times[']
            };

            const exact = sectionExactFields[section] || [];
            if (exact.includes(fieldName)) {
                return true;
            }

            const prefixes = sectionPrefixes[section] || [];
            return prefixes.some(prefix => fieldName.startsWith(prefix));
        }

        function prepareSectionOnlySubmit(section, submitter) {
            if (!section) return;

            const form = document.getElementById('costingForm');
            if (!form) return;

            form.querySelectorAll('input, select, textarea, button').forEach((el) => {
                if (el === submitter) {
                    return;
                }

                if (!el.name) {
                    return;
                }

                if (shouldKeepFieldForSection(el.name, section)) {
                    return;
                }

                if (!el.disabled) {
                    el.dataset.sectionDisabled = '1';
                    el.disabled = true;
                }
            });
        }

        function showPartlistImportConfirmModal() {
            return new Promise((resolve) => {
                const modal = document.getElementById('partlistImportConfirmModal');
                const okBtn = document.getElementById('partlistImportOkBtn');
                const cancelBtn = document.getElementById('partlistImportCancelBtn');

                if (!modal || !okBtn || !cancelBtn) {
                    resolve(false);
                    return;
                }

                const closeWith = (result) => {
                    modal.classList.add('is-hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    okBtn.removeEventListener('click', handleOk);
                    cancelBtn.removeEventListener('click', handleCancel);
                    modal.removeEventListener('click', handleOverlay);
                    document.removeEventListener('keydown', handleEsc);
                    resolve(result);
                };

                const handleOk = () => closeWith(true);
                const handleCancel = () => closeWith(false);
                const handleOverlay = (event) => {
                    if (event.target === modal) {
                        closeWith(false);
                    }
                };
                const handleEsc = (event) => {
                    if (event.key === 'Escape') {
                        closeWith(false);
                    }
                };

                modal.classList.remove('is-hidden');
                modal.setAttribute('aria-hidden', 'false');

                okBtn.addEventListener('click', handleOk);
                cancelBtn.addEventListener('click', handleCancel);
                modal.addEventListener('click', handleOverlay);
                document.addEventListener('keydown', handleEsc);
            });
        }

        async function triggerPartlistImport() {
            const fileInput = document.getElementById('importPartlistFileInput');
            if (!fileInput) return;

            const hasFilledMaterial = Array.from(document.querySelectorAll('#materialTableBody tr')).some((row) => {
                const partNo = (row.querySelector('.part-no')?.value || '').trim();
                const partName = (row.querySelector('.part-name')?.value || '').trim();
                const amount1 = parseInputNumber(row.querySelector('.amount1')?.value || 0);
                const qtyReq = parseInputNumber(row.querySelector('.qty-req')?.value || 0);
                return partNo !== '' || partName !== '' || amount1 > 0 || qtyReq > 0;
            });

            if (hasFilledMaterial) {
                const confirmed = await showPartlistImportConfirmModal();
                if (!confirmed) {
                    return;
                }
            }

            fileInput.value = '';
            fileInput.click();
        }

        function submitPartlistImport() {
            const form = document.getElementById('partlistImportForm');
            const importForecast = document.getElementById('importForecast');
            const importProjectPeriod = document.getElementById('importProjectPeriod');
            const importWireRateId = document.getElementById('importWireRateId');
            const forecastHidden = document.getElementById('forecast');
            const projectPeriod = document.getElementById('projectPeriod');
            const wireRateSelector = document.getElementById('wireRateSelector');

            if (!form) return;

            syncForecastHidden();

            if (importForecast && forecastHidden) {
                importForecast.value = forecastHidden.value || '0';
            }

            if (importProjectPeriod && projectPeriod) {
                importProjectPeriod.value = projectPeriod.value || '0';
            }

            if (importWireRateId && wireRateSelector) {
                importWireRateId.value = wireRateSelector.value || '';
            }

            // Sync main form fields to import form
            const syncFields = {
                'importBusinessCategoryId': 'select[name="business_category_id"]',
                'importCustomerId': 'select[name="customer_id"]',
                'importPeriod': '#periodInput',
                'importLine': 'select[name="line"]',
                'importModel': 'input[name="model"]',
                'importAssyNo': 'input[name="assy_no"]',
                'importAssyName': 'input[name="assy_name"]',
                'importRateUsd': '#rateUSD',
                'importRateJpy': '#rateJPY',
                'importLmeRate': '#lmeRate',
            };
            for (const [hiddenId, mainSelector] of Object.entries(syncFields)) {
                const hidden = document.getElementById(hiddenId);
                const main = document.querySelector('#costingForm ' + mainSelector);
                if (hidden && main) hidden.value = main.value || '';
            }

            // Submit the import form
            showAppLoading('Mengimport partlist...');

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            form.submit();
        }

        // Initialize calculations on page load
        document.addEventListener('DOMContentLoaded', function () {
            initSectionToggles();
            bindMaterialTableBehaviors();
            initMaterialFilterPopup();
            initMaterialHeaderFilters();
            normalizeMaterialTextInputs();
            markMaterialControlsUndoBase();
            applyMaterialFilters();
            updateMaterialSelectAllRowsState();
            formatForecastDisplay();

            syncMaterialTableFromRenderedValues();
            calculateTotals();

            refreshUnpricedRecap();
            bindUnpricedManualPriceInputs();
            bindUnpricedDeleteButtons();
            bindUnpricedAddPriceButtons();

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
                costingForm.addEventListener('submit', function (event) {
                    normalizeMaterialTextInputs();
                    syncForecastHidden();
                    refreshUnpricedRecap();

                    const submitter = event.submitter;
                    const updateSectionInput = document.getElementById('updateSectionInput');
                    const section = submitter?.dataset?.section || '';

                    if (updateSectionInput) {
                        updateSectionInput.value = section;
                    }

                    if (section) {
                        prepareSectionOnlySubmit(section, submitter);
                    }
                });
            }
        });

        // Recalculate when exchange rates change
        document.getElementById('rateUSD').addEventListener('change', syncMaterialTableFromRenderedValues);
        document.getElementById('rateJPY').addEventListener('change', syncMaterialTableFromRenderedValues);
        document.getElementById('forecastDisplay').addEventListener('change', function () {
            formatForecastDisplay();
            syncMaterialTableFromRenderedValues();
        });
        document.getElementById('projectPeriod').addEventListener('change', syncMaterialTableFromRenderedValues);

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

        function normalizeMaterialRowsToReasonableValues() {
            const rows = document.querySelectorAll('#materialTableBody tr');

            rows.forEach((row) => {
                const qtyReqInput = row.querySelector('.qty-req');
                const amount1Input = row.querySelector('.amount1');
                const qtyMoqInput = row.querySelector('.qty-moq');
                const currencySelect = row.querySelector('.currency');
                const importTaxInput = row.querySelector('.import-tax');
                const amount2Element = row.querySelector('.amount2');
                const totalPriceElement = row.querySelector('.total-price');
                const multiplyFactorElement = row.querySelector('.multiply-factor');
                const unitInput = row.querySelector('.unit');

                if (!qtyReqInput || !amount1Input || !qtyMoqInput || !currencySelect || !amount2Element || !totalPriceElement) {
                    return;
                }

                let qtyReq = parseInputNumber(qtyReqInput.value || 0);
                let moq = parseInputNumber(qtyMoqInput.value || 0);
                const currency = String(currencySelect.value || 'IDR').toUpperCase();
                const importTax = parseInputNumber(importTaxInput?.value || 0);
                const rate = getExchangeRate(currency);
                const taxFactor = Math.max(1e-9, 1 + (importTax / 100));
                const multiplyFactor = Math.max(1e-9, parseInputNumber(multiplyFactorElement?.textContent || 1));
                const unit = String(unitInput?.value || '').toUpperCase();
                const unitDivisor = (unit === 'METER' || unit === 'M' || unit === 'MTR' || unit === 'MM') ? 1000 : 1;
                const currentTotal = parseInputNumber(totalPriceElement.getAttribute('data-value') || totalPriceElement.textContent || 0);

                // Normalize unreasonable qty values from corrupted inputs/imports.
                if (qtyReq > 1000) {
                    qtyReq = 1;
                }
                if (qtyReq <= 0) {
                    qtyReq = 1;
                }

                // Normalize MOQ so multiply factor stays realistic.
                if (moq <= 0 || moq > (qtyReq * 20)) {
                    moq = Math.max(qtyReq, qtyReq * 5);
                }

                const denom = Math.max(1e-9, qtyReq * Math.max(1, rate));
                const normalizedAmount2 = currentTotal > 0
                    ? (currentTotal / denom)
                    : parseInputNumber(amount2Element.textContent || 0);

                const normalizedAmount1 = (normalizedAmount2 * Math.max(1, unitDivisor)) / (multiplyFactor * taxFactor);

                qtyReqInput.value = floatToInput(Math.round(qtyReq));
                amount1Input.value = floatToInput(normalizedAmount1.toFixed(4));
                qtyMoqInput.value = floatToInput(Number(moq.toFixed(4)));
                amount2Element.textContent = Number(normalizedAmount2.toFixed(4)).toFixed(4);
            });
        }

        function restoreMaterialRowsFromDatabase() {
            const rows = document.querySelectorAll('#materialTableBody tr');

            rows.forEach(row => {
                const qtyReqInput = row.querySelector('.qty-req');
                const amount1Input = row.querySelector('.amount1');
                const qtyMoqInput = row.querySelector('.qty-moq');
                const amount2Element = row.querySelector('.amount2');
                const totalPriceElement = row.querySelector('.total-price');

                if (qtyReqInput && qtyReqInput.dataset.originalQtyReq !== undefined) {
                    qtyReqInput.value = floatToInput(qtyReqInput.dataset.originalQtyReq || 0);
                }

                if (amount1Input && amount1Input.dataset.originalAmount1 !== undefined) {
                    amount1Input.value = floatToInput(amount1Input.dataset.originalAmount1 || 0);
                }

                if (qtyMoqInput && qtyMoqInput.dataset.originalMoq !== undefined) {
                    qtyMoqInput.value = floatToInput(qtyMoqInput.dataset.originalMoq || 0);
                }

                if (amount2Element && amount2Element.dataset.originalAmount2 !== undefined) {
                    amount2Element.textContent = Number(amount2Element.dataset.originalAmount2 || 0).toFixed(4);
                }

                if (amount1Input && totalPriceElement && amount1Input.dataset.originalAmount1 !== undefined) {
                    const amount1Value = parseInputNumber(amount1Input.dataset.originalAmount1 || 0);
                    totalPriceElement.textContent = formatRupiah(amount1Value);
                    totalPriceElement.setAttribute('data-value', amount1Value);
                }
            });

            calculateTableTotal(false);
        }

        function syncMaterialTableFromRenderedValues() {
            const rows = document.querySelectorAll('#materialTableBody tr');
            rows.forEach((row) => {
                const totalPriceElement = row.querySelector('.total-price');
                const amount1Input = row.querySelector('.amount1');
                if (totalPriceElement && amount1Input) {
                    const amount1Value = parseInputNumber(amount1Input.value || 0);
                    totalPriceElement.textContent = formatRupiah(amount1Value);
                    totalPriceElement.setAttribute('data-value', amount1Value);
                }
            });

            calculateTableTotal(false);
        }

    </script>
@endsection