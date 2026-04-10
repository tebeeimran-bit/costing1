@extends('layouts.app')

@section('title', 'Database Dokumen Project')
@section('page-title', 'Database Dokumen Project')

@section('breadcrumb')
    <a href="{{ route('database.parts', absolute: false) }}">Database</a>
    <span class="breadcrumb-separator">/</span>
    <span>Dokumen Project</span>
@endsection

@section('content')
    <style>
        .doc-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .doc-status-badge.ada { color: #065f46; background: #d1fae5; }
        .doc-status-badge.belum { color: #92400e; background: #fef3c7; }
        .td-a00 .doc-status-badge.ada { color: #1e40af; background: #dbeafe; }
        .td-a04 .doc-status-badge.ada { color: #991b1b; background: #fee2e2; }
        .td-a05 .doc-status-badge.ada { color: #166534; background: #dcfce7; }
        .td-a00 .doc-status-badge.belum { color: #6b7280; background: #e8edf4; }
        .td-a04 .doc-status-badge.belum { color: #6b7280; background: #f5e6e6; }
        .td-a05 .doc-status-badge.belum { color: #6b7280; background: #e6f0e8; }
        .doc-download-link {
            color: var(--blue-600);
            text-decoration: none;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .doc-download-link:hover { text-decoration: underline; }
        .doc-summary-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .doc-summary-card {
            padding: 1.25rem;
            border-radius: 12px;
            color: #fff;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .doc-summary-card .doc-label { font-size: 0.78rem; font-weight: 600; opacity: 0.9; }
        .doc-summary-card .doc-count { font-size: 1.75rem; font-weight: 800; }
        .doc-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            margin-bottom: 1rem;
        }
        .doc-filter-bar .form-input,
        .doc-filter-bar .form-select { max-width: 280px; }
        @media (max-width: 768px) {
            .doc-summary-cards { grid-template-columns: 1fr; }
        }
        /* Modal */
        .doc-modal {
            position: fixed; inset: 0; z-index: 1000;
            display: flex; align-items: center; justify-content: center;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(2px);
        }
        .doc-modal.is-hidden { display: none; }
        .doc-modal-content {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 560px;
            padding: 1.5rem;
        }
        .doc-modal-content.doc-modal-wide {
            max-width: 820px;
        }
        .doc-modal-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1rem;
        }
        .doc-modal-title { font-size: 1.1rem; font-weight: 700; color: var(--slate-800); }
        .doc-modal-close {
            border: 0; background: var(--slate-100); border-radius: 8px;
            padding: 0.4rem; cursor: pointer; color: var(--slate-500);
        }
        .doc-modal-close:hover { background: var(--slate-200); }
        .doc-form-group { margin-bottom: 0.75rem; }
        .doc-form-group label { display: block; font-size: 0.72rem; font-weight: 600; color: var(--slate-600); text-transform: uppercase; margin-bottom: 0.3rem; }
        .doc-form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--slate-200); }
        .doc-section-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }
        .doc-section-col {
            padding: 0.85rem;
            border-radius: 10px;
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
        }
        .doc-section-title {
            font-size: 0.78rem; font-weight: 700;
            margin: 0 0 0.65rem; padding-bottom: 0.35rem;
            border-bottom: 2px solid var(--slate-200);
        }
        .doc-section-col:nth-child(1) .doc-section-title { color: #2563eb; border-color: #93c5fd; }
        .doc-section-col:nth-child(2) .doc-section-title { color: #dc2626; border-color: #fca5a5; }
        .doc-section-col:nth-child(3) .doc-section-title { color: #16a34a; border-color: #86efac; }
        .btn-action {
            display: inline-flex; align-items: center; justify-content: center;
            border: 0; border-radius: 6px; padding: 0.35rem; cursor: pointer;
            transition: background 0.15s;
        }
        .btn-action.btn-edit { background: #dbeafe; color: #2563eb; }
        .btn-action.btn-edit:hover { background: #bfdbfe; }
        .btn-action.btn-delete { background: #fee2e2; color: #dc2626; }
        .btn-action.btn-delete:hover { background: #fecaca; }
        .delete-modal-body { text-align: center; padding: 1rem 0; }
        .delete-modal-text { font-size: 0.9rem; color: var(--slate-600); margin-bottom: 0.5rem; }
        .delete-modal-name { font-weight: 700; color: var(--slate-800); }
        /* Pagination */
        .doc-pagination .pagination { display: flex; gap: 0.25rem; margin: 0; list-style: none; padding: 0; }
        .doc-pagination .page-item .page-link {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 2rem; height: 2rem; padding: 0 0.5rem;
            border-radius: 6px; border: 1px solid var(--slate-200);
            font-size: 0.82rem; font-weight: 600; color: var(--slate-600);
            background: #fff; text-decoration: none; transition: all 0.15s;
        }
        .doc-pagination .page-item .page-link:hover { background: #eff6ff; color: #2563eb; border-color: #93c5fd; }
        .doc-pagination .page-item.active .page-link { background: #2563eb; color: #fff; border-color: #2563eb; }
        .doc-pagination .page-item.disabled .page-link { opacity: 0.4; pointer-events: none; }
        /* Color-coded table columns */
        .th-a00 { background: #2563eb !important; color: #fff !important; }
        .th-a04 { background: #dc2626 !important; color: #fff !important; }
        .th-a05 { background: #16a34a !important; color: #fff !important; }
        .td-a00 { background: #eff6ff; }
        .td-a04 { background: #fef2f2; }
        .td-a05 { background: #f0fdf4; }
    </style>

    @if(session('success'))
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #a7f3d0;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div style="background: #fef3c7; color: #92400e; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #fde68a;">
            {{ session('warning') }}
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="doc-summary-cards">
        <div class="doc-summary-card" style="background: #2563eb;">
            <span class="doc-label">A00 (RFQ/RFI)</span>
            <span class="doc-count">{{ $a00Count }}</span>
        </div>
        <div class="doc-summary-card" style="background: #dc2626;">
            <span class="doc-label">A04 (Cancelled/Failed)</span>
            <span class="doc-count">{{ $a04Count }}</span>
        </div>
        <div class="doc-summary-card" style="background: #16a34a;">
            <span class="doc-label">A05 (Die Go)</span>
            <span class="doc-count">{{ $a05Count }}</span>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('database.project-documents') }}" id="docFilterForm">
    <div class="doc-filter-bar">
        <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.2rem 0.8rem; border: 1px solid var(--slate-200); border-radius: 12px; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04); max-width: 420px; width: 100%;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--slate-400); flex-shrink: 0;">
                <circle cx="11" cy="11" r="7"></circle>
                <path d="M20 20l-3.5-3.5"></path>
            </svg>
            <input
                type="text"
                name="search"
                id="docSearchInput"
                placeholder="Cari customer, model, part name..."
                value="{{ $search }}"
                style="border: 0; outline: none; width: 100%; padding: 0.7rem 0; font-size: 0.95rem; color: var(--slate-800); background: transparent;"
            >
        </div>
        <select name="status" id="docFilterStatus" class="form-select" onchange="document.getElementById('docFilterForm').submit()" style="padding: 0.55rem 0.75rem; font-size: 0.85rem;">
            <option value="" {{ $statusFilter === '' ? 'selected' : '' }}>Semua Dokumen</option>
            <option value="a00" {{ $statusFilter === 'a00' ? 'selected' : '' }}>A00 (RFQ/RFI)</option>
            <option value="a04" {{ $statusFilter === 'a04' ? 'selected' : '' }}>A04 (Cancelled/Failed)</option>
            <option value="a05" {{ $statusFilter === 'a05' ? 'selected' : '' }}>A05 (Die Go)</option>
        </select>
        <select name="per_page" class="form-select" onchange="document.getElementById('docFilterForm').submit()" style="padding: 0.55rem 0.75rem; font-size: 0.85rem; width: auto;">
            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 / hal</option>
            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 / hal</option>
            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / hal</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / hal</option>
        </select>
        <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1rem; font-size: 0.85rem; white-space:nowrap;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            Cari
        </button>
        @if($search || $statusFilter)
        <a href="{{ route('database.project-documents') }}" class="btn btn-secondary" style="padding: 0.55rem 1rem; font-size: 0.85rem;">Reset</a>
        @endif
    </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pengumpulan Dokumen A00, A04 &amp; A05</h3>
            <span style="font-size: 0.8rem; color: var(--slate-500);">Menampilkan {{ $pagedRows->firstItem() }}–{{ $pagedRows->lastItem() }} dari {{ $pagedRows->total() }} data</span>
        </div>
        <div class="material-table-container">
            <table class="data-table" id="docProjectTable" style="min-width: 1200px;">
                <thead>
                    <tr>
                        <th style="width: 40px;">No.</th>
                        <th>Customer</th>
                        <th>Model</th>
                        <th>Part Name</th>
                        <th>Revisi</th>
                        <th class="th-a00" style="text-align: center;">A00</th>
                        <th class="th-a00">Tgl Diterima A00</th>
                        <th class="th-a00">Dokumen A00</th>
                        <th class="th-a04" style="text-align: center;">A04</th>
                        <th class="th-a04">Tgl Diterima A04</th>
                        <th class="th-a04">Dokumen A04</th>
                        <th class="th-a05" style="text-align: center;">A05</th>
                        <th class="th-a05">Tgl Diterima A05</th>
                        <th class="th-a05">Dokumen A05</th>
                        <th style="width: 80px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagedRows as $index => $row)
                        @php
                            $rev = $row->revision;
                            $project = $row->project;
                            $costing = $row->costingData;
                            $hasA00 = ($rev->a00 ?? '') === 'ada';
                            $hasA04 = ($rev->a04 ?? '') === 'ada';
                            $hasA05 = ($rev->a05 ?? '') === 'ada';
                            $priorityStatus = $row->status;
                        @endphp
                        <tr data-search="{{ strtolower(implode(' ', array_filter([
                            $project->customer ?? '',
                            $costing->customer->name ?? '',
                            $project->model ?? '',
                            $costing->model ?? '',
                            $project->part_name ?? '',
                            $costing->assy_name ?? '',
                            $rev->version_label ?? '',
                        ]))) }}"
                        data-status="{{ $priorityStatus }}">
                            <td>{{ $pagedRows->firstItem() + $loop->index }}</td>
                            <td>{{ $costing->customer->name ?? $project->customer ?? '-' }}</td>
                            <td>{{ $costing->model ?? $project->model ?? '-' }}</td>
                            <td>{{ $costing->assy_name ?? $project->part_name ?? '-' }}</td>
                            <td>{{ $rev->version_label ?? '-' }}</td>

                            {{-- A00 --}}
                            <td class="td-a00" style="text-align: center;">
                                <span class="doc-status-badge {{ $hasA00 ? 'ada' : 'belum' }}">
                                    @if($hasA00)
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                        Ada
                                    @else
                                        Belum
                                    @endif
                                </span>
                            </td>
                            <td class="td-a00">{{ $hasA00 && $rev->a00_received_date ? $rev->a00_received_date->format('d M Y') : '-' }}</td>
                            <td class="td-a00">
                                @if($hasA00 && $rev->a00_document_file_path)
                                    <a href="{{ route('tracking-documents.download', [$rev->id, 'a00']) }}" class="doc-download-link" title="{{ $rev->a00_document_original_name }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        {{ Str::limit($rev->a00_document_original_name, 25) }}
                                    </a>
                                @else
                                    <span style="color: var(--slate-400);">-</span>
                                @endif
                            </td>

                            {{-- A04 --}}
                            <td class="td-a04" style="text-align: center;">
                                <span class="doc-status-badge {{ $hasA04 ? 'ada' : 'belum' }}">
                                    @if($hasA04)
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                        Ada
                                    @else
                                        Belum
                                    @endif
                                </span>
                            </td>
                            <td class="td-a04">{{ $hasA04 && $rev->a04_received_date ? $rev->a04_received_date->format('d M Y') : '-' }}</td>
                            <td class="td-a04">
                                @if($hasA04 && $rev->a04_document_file_path)
                                    <a href="{{ route('tracking-documents.download', [$rev->id, 'a04']) }}" class="doc-download-link" title="{{ $rev->a04_document_original_name }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        {{ Str::limit($rev->a04_document_original_name, 25) }}
                                    </a>
                                @else
                                    <span style="color: var(--slate-400);">-</span>
                                @endif
                            </td>

                            {{-- A05 --}}
                            <td class="td-a05" style="text-align: center;">
                                <span class="doc-status-badge {{ $hasA05 ? 'ada' : 'belum' }}">
                                    @if($hasA05)
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                        Ada
                                    @else
                                        Belum
                                    @endif
                                </span>
                            </td>
                            <td class="td-a05">{{ $hasA05 && $rev->a05_received_date ? $rev->a05_received_date->format('d M Y') : '-' }}</td>
                            <td class="td-a05">
                                @if($hasA05 && $rev->a05_document_file_path)
                                    <a href="{{ route('tracking-documents.download', [$rev->id, 'a05']) }}" class="doc-download-link" title="{{ $rev->a05_document_original_name }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        {{ Str::limit($rev->a05_document_original_name, 25) }}
                                    </a>
                                @else
                                    <span style="color: var(--slate-400);">-</span>
                                @endif
                            </td>
                            {{-- Aksi --}}
                            <td style="text-align: center; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 0.35rem;">
                                    <button type="button" class="btn-action btn-edit" title="Edit Dokumen"
                                        onclick="openEditDocModal({{ $rev->id }}, {{ json_encode([
                                            'customer' => $costing->customer->name ?? $project->customer ?? '-',
                                            'model' => $costing->model ?? $project->model ?? '-',
                                            'part_name' => $costing->assy_name ?? $project->part_name ?? '-',
                                            'a00' => $rev->a00 ?? '',
                                            'a00_received_date' => $hasA00 && $rev->a00_received_date ? $rev->a00_received_date->format('Y-m-d') : '',
                                            'a00_doc' => $rev->a00_document_original_name ?? '',
                                            'a04' => $rev->a04 ?? '',
                                            'a04_received_date' => $hasA04 && $rev->a04_received_date ? $rev->a04_received_date->format('Y-m-d') : '',
                                            'a04_doc' => $rev->a04_document_original_name ?? '',
                                            'a05' => $rev->a05 ?? '',
                                            'a05_received_date' => $hasA05 && $rev->a05_received_date ? $rev->a05_received_date->format('Y-m-d') : '',
                                            'a05_doc' => $rev->a05_document_original_name ?? '',
                                        ]) }})">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </button>
                                    <button type="button" class="btn-action btn-delete" title="Hapus Dokumen"
                                        onclick="openDeleteDocModal({{ $rev->id }}, '{{ addslashes($costing->assy_name ?? $project->part_name ?? '-') }}')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" style="text-align: center; color: var(--slate-400); padding: 2rem;">
                                Belum ada data dokumen project.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pagedRows->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--slate-200); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div style="font-size: 0.82rem; color: var(--slate-500);">
                Halaman {{ $pagedRows->currentPage() }} dari {{ $pagedRows->lastPage() }}
                &nbsp;·&nbsp; {{ $pagedRows->total() }} data
            </div>
            <div class="doc-pagination">
                {{ $pagedRows->links('pagination.doc-paginator') }}
            </div>
        </div>
        @endif
    </div>

    {{-- Edit Modal --}}
    <div id="editDocModal" class="doc-modal is-hidden" onclick="if(event.target===this)closeEditDocModal()">
        <div class="doc-modal-content doc-modal-wide">
            <div class="doc-modal-header">
                <h3 class="doc-modal-title">Edit Dokumen Project</h3>
                <button type="button" class="doc-modal-close" onclick="closeEditDocModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div style="background: var(--slate-50); padding: 0.6rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.82rem; color: var(--slate-600); border: 1px solid var(--slate-200);">
                <strong id="editDocLabel"></strong>
            </div>
            <form id="editDocForm" method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('editA00Status').disabled=false;">
                @csrf
                @method('PUT')

                <div class="doc-section-grid">
                    {{-- A00 Column --}}
                    <div class="doc-section-col">
                        <div class="doc-section-title">A00 (RFQ/RFI)</div>
                        <div class="doc-form-group">
                            <label>Status</label>
                            <select name="a00" id="editA00Status" class="form-select" onchange="toggleEditDateWrap('a00')">
                                <option value="belum_ada">Belum Ada</option>
                                <option value="ada">Ada</option>
                            </select>
                        </div>
                        <div id="editA00DateWrap" style="display:none;">
                            <div class="doc-form-group">
                                <label>Tanggal Diterima</label>
                                <input type="date" name="a00_received_date" id="editA00Date" class="form-input">
                            </div>
                            <div class="doc-form-group">
                                <label>Dokumen (PDF)</label>
                                <input type="file" name="a00_document_file" accept=".pdf" class="form-input" style="font-size:0.75rem;">
                                <small id="editA00DocName" style="color: var(--slate-500); font-size: 0.72rem;"></small>
                            </div>
                        </div>
                    </div>

                    {{-- A04 Column --}}
                    <div class="doc-section-col">
                        <div class="doc-section-title">A04 (Cancelled/Failed)</div>
                        <div class="doc-form-group">
                            <label>Status</label>
                            <select name="a04" id="editA04Status" class="form-select" onchange="toggleEditDateWrap('a04')">
                                <option value="belum_ada">Belum Ada</option>
                                <option value="ada">Ada</option>
                            </select>
                        </div>
                        <div id="editA04DateWrap" style="display:none;">
                            <div class="doc-form-group">
                                <label>Tanggal Diterima</label>
                                <input type="date" name="a04_received_date" id="editA04Date" class="form-input">
                            </div>
                            <div class="doc-form-group">
                                <label>Dokumen (PDF)</label>
                                <input type="file" name="a04_document_file" accept=".pdf" class="form-input" style="font-size:0.75rem;">
                                <small id="editA04DocName" style="color: var(--slate-500); font-size: 0.72rem;"></small>
                            </div>
                        </div>
                    </div>

                    {{-- A05 Column --}}
                    <div class="doc-section-col">
                        <div class="doc-section-title">A05 (Die Go)</div>
                        <div class="doc-form-group">
                            <label>Status</label>
                            <select name="a05" id="editA05Status" class="form-select" onchange="toggleEditDateWrap('a05')">
                                <option value="belum_ada">Belum Ada</option>
                                <option value="ada">Ada</option>
                            </select>
                        </div>
                        <div id="editA05DateWrap" style="display:none;">
                            <div class="doc-form-group">
                                <label>Tanggal Diterima</label>
                                <input type="date" name="a05_received_date" id="editA05Date" class="form-input">
                            </div>
                            <div class="doc-form-group">
                                <label>Dokumen (PDF)</label>
                                <input type="file" name="a05_document_file" accept=".pdf" class="form-input" style="font-size:0.75rem;">
                                <small id="editA05DocName" style="color: var(--slate-500); font-size: 0.72rem;"></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="doc-form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditDocModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div id="deleteDocModal" class="doc-modal is-hidden" onclick="if(event.target===this)closeDeleteDocModal()">
        <div class="doc-modal-content" style="max-width: 420px;">
            <div class="doc-modal-header">
                <h3 class="doc-modal-title">Konfirmasi Hapus</h3>
                <button type="button" class="doc-modal-close" onclick="closeDeleteDocModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="delete-modal-body">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" style="margin-bottom: 0.75rem;">
                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <p class="delete-modal-text">Apakah Anda yakin ingin menghapus semua dokumen (A00, A04, A05) untuk:</p>
                <p class="delete-modal-name" id="deleteDocName"></p>
            </div>
            <form id="deleteDocForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="doc-form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteDocModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #ef4444, #dc2626); border-color: #dc2626;">Hapus Dokumen</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function toggleEditDateWrap(prefix) {
        const status = document.getElementById('edit' + prefix.charAt(0).toUpperCase() + prefix.slice(1) + 'Status').value;
        const wrap = document.getElementById('edit' + prefix.charAt(0).toUpperCase() + prefix.slice(1) + 'DateWrap');
        wrap.style.display = status === 'ada' ? '' : 'none';

        applyBusinessRules(prefix);
    }

    function applyBusinessRules(changedPrefix) {
        const a00 = document.getElementById('editA00Status');
        const a04 = document.getElementById('editA04Status');
        const a05 = document.getElementById('editA05Status');

        // Rule: A04 and A05 are mutually exclusive
        if (changedPrefix === 'a04' && a04.value === 'ada') {
            a05.value = 'belum_ada';
            toggleEditDateWrap('a05');
        }
        if (changedPrefix === 'a05' && a05.value === 'ada') {
            a04.value = 'belum_ada';
            toggleEditDateWrap('a04');
        }

        // Rule: If A04 or A05 = ada, force A00 = ada
        if (a04.value === 'ada' || a05.value === 'ada') {
            a00.value = 'ada';
            a00.disabled = true;
            a00.title = 'A00 otomatis "Ada" karena A04/A05 sudah ada';
            a00.style.opacity = '0.6';
            document.getElementById('editA00DateWrap').style.display = '';
        } else {
            a00.disabled = false;
            a00.title = '';
            a00.style.opacity = '1';
        }
    }

    function openEditDocModal(revisionId, data) {
        const baseUrl = '{{ url("database/project-documents") }}';
        document.getElementById('editDocForm').action = baseUrl + '/' + revisionId;
        document.getElementById('editDocLabel').textContent = data.customer + ' — ' + data.model + ' — ' + data.part_name;

        // A00
        document.getElementById('editA00Status').value = data.a00 === 'ada' ? 'ada' : 'belum_ada';
        document.getElementById('editA00Date').value = data.a00_received_date || '';
        document.getElementById('editA00DocName').textContent = data.a00_doc ? 'File saat ini: ' + data.a00_doc : '';
        toggleEditDateWrap('a00');

        // A04
        document.getElementById('editA04Status').value = data.a04 === 'ada' ? 'ada' : 'belum_ada';
        document.getElementById('editA04Date').value = data.a04_received_date || '';
        document.getElementById('editA04DocName').textContent = data.a04_doc ? 'File saat ini: ' + data.a04_doc : '';
        toggleEditDateWrap('a04');

        // A05
        document.getElementById('editA05Status').value = data.a05 === 'ada' ? 'ada' : 'belum_ada';
        document.getElementById('editA05Date').value = data.a05_received_date || '';
        document.getElementById('editA05DocName').textContent = data.a05_doc ? 'File saat ini: ' + data.a05_doc : '';
        toggleEditDateWrap('a05');

        // Apply business rules after loading values
        applyBusinessRules('');

        document.getElementById('editDocModal').classList.remove('is-hidden');
    }

    function closeEditDocModal() {
        document.getElementById('editDocModal').classList.add('is-hidden');
    }

    function openDeleteDocModal(revisionId, name) {
        const baseUrl = '{{ url("database/project-documents") }}';
        document.getElementById('deleteDocForm').action = baseUrl + '/' + revisionId;
        document.getElementById('deleteDocName').textContent = name;
        document.getElementById('deleteDocModal').classList.remove('is-hidden');
    }

    function closeDeleteDocModal() {
        document.getElementById('deleteDocModal').classList.add('is-hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeEditDocModal();
            closeDeleteDocModal();
        }
    });
</script>
@endsection
