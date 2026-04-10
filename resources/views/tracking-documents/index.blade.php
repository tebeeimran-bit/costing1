@extends('layouts.app')

@section('title', 'Project')
@section('page-title', 'Project')

@section('breadcrumb')
    <a href="{{ route('dashboard', absolute: false) }}">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <span>Project</span>
@endsection

@section('content')
    <style>
        .tracking-page {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .table-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            gap: 1rem;
        }

        .table-controls {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .table-search-input {
            width: 240px;
            max-width: 100%;
            border: 1px solid var(--slate-300);
            border-radius: 0.6rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.82rem;
            color: var(--slate-700);
            background: #fff;
        }

        .table-search-input:focus {
            outline: none;
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.25);
        }

        .table-wrap {
            overflow-x: auto;
            overflow-y: hidden;
            border: 1px solid var(--slate-200);
            border-radius: 0.75rem;
            background: white;
        }

        .tracking-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 0;
            table-layout: fixed;
        }

        .tracking-table th:nth-child(2),
        .tracking-table td:nth-child(2) {
            width: 38%;
            min-width: 380px;
        }

        .tracking-table th:nth-child(2) {
            text-align: center;
        }

        .tracking-table th:last-child,
        .tracking-table td:last-child {
            width: 164px;
            min-width: 164px;
        }

        .tracking-table th:nth-child(6),
        .tracking-table td:nth-child(6) {
            width: 170px;
            min-width: 170px;
        }

        .tracking-table th:nth-child(7),
        .tracking-table td:nth-child(7) {
            width: 130px;
            min-width: 130px;
        }

        .tracking-table td:nth-child(6) {
            text-align: center;
        }

        .tracking-table td:nth-child(7) {
            white-space: normal;
            word-break: keep-all;
            text-align: center;
        }

        .tracking-table th,
        .tracking-table td {
            border-bottom: 1px solid var(--slate-200);
            padding: 0.7rem;
            text-align: left;
            font-size: 0.85rem;
            vertical-align: top;
        }

        .tracking-table th {
            background: var(--slate-50);
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--slate-600);
            white-space: normal;
        }

        .tracking-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .tracking-table tbody tr:hover {
            background: #fafcff;
        }

        .project-info {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.55rem 0.9rem;
            min-width: 0;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            border: 1px solid #e2e8f0;
            border-radius: 0.7rem;
            padding: 0.65rem 0.7rem;
        }

        .project-item {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            align-items: start;
            min-width: 0;
        }

        .project-label {
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 1.4;
        }

        .project-value {
            color: #0f172a;
            font-weight: 600;
            line-height: 1.4;
            min-width: 0;
            overflow-wrap: break-word;
            word-break: normal;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.25rem 0.6rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
            max-width: 100%;
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.15);
            color: #b45309;
        }

        .status-generated {
            background: rgba(37, 99, 235, 0.14);
            color: #1d4ed8;
        }

        .status-warning {
            background: rgba(249, 115, 22, 0.16);
            color: #c2410c;
        }

        .status-submitted {
            background: rgba(34, 197, 94, 0.16);
            color: #15803d;
        }

        .action-group {
            display: grid;
            gap: 0.36rem;
            min-width: 124px;
            justify-items: end;
            margin-left: auto;
            align-items: start;
        }

        .action-group .btn,
        .action-group form {
            width: 120px;
        }

        .action-group form .btn {
            width: 100%;
        }

        .action-group .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.34rem;
            white-space: nowrap;
            justify-content: center;
            border-radius: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            transition: all 0.18s ease;
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.06);
        }

        .action-group .btn-secondary {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .action-group .btn-secondary:hover {
            background: #eef2ff;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }

        .action-group .btn-primary {
            box-shadow: 0 6px 14px rgba(37, 99, 235, 0.25);
        }

        .action-group .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.28);
        }

        .action-delete {
            color: #b91c1c;
            border-color: #fecaca;
            background: #fff1f2;
        }

        .action-delete:hover {
            background: #ffe4e6;
            border-color: #fda4af;
            transform: translateY(-1px);
        }

        .action-icon {
            width: 13px;
            height: 13px;
            flex: 0 0 auto;
            opacity: 0.9;
        }

        .btn-sm {
            padding: 0.42rem 0.72rem;
            font-size: 0.75rem;
        }

        .receipt-form-card.is-hidden,
        .modal.is-hidden {
            display: none;
        }

        .receipt-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .project-edit-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .project-edit-grid .form-group,
        .project-edit-grid .form-input,
        .project-edit-grid .form-select {
            min-width: 0;
            width: 100%;
        }

        .quantity-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.45rem;
            min-width: 0;
        }

        .receipt-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .file-input-wrap {
            position: relative;
            border: 1px solid var(--slate-300);
            border-radius: 0.5rem;
            background: #fff;
            min-height: 2.7rem;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .file-input-wrap input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }

        .file-input-content {
            display: flex;
            align-items: center;
            width: 100%;
            min-width: 0;
            padding: 0.45rem 0.55rem;
            gap: 0.6rem;
        }

        .file-input-btn {
            flex: 0 0 auto;
            border: 1px solid var(--slate-300);
            border-radius: 0.4rem;
            background: var(--slate-50);
            color: var(--slate-700);
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.32rem 0.6rem;
            line-height: 1.2;
        }

        .file-input-name {
            min-width: 0;
            font-size: 0.82rem;
            color: var(--slate-600);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .field-help {
            display: block;
            margin-top: 0.4rem;
            font-size: 0.76rem;
            color: var(--slate-500);
        }

        .field-error {
            display: none;
            margin-top: 0.35rem;
            font-size: 0.76rem;
            color: #dc2626;
            font-weight: 600;
        }

        .field-error.is-visible {
            display: block;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
        }

        .modal {
            position: fixed;
            inset: 0;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.55);
            padding: 1rem;
        }

        .modal-content {
            background: white;
            width: min(980px, 100%);
            max-height: 85vh;
            overflow: auto;
            border-radius: 0.9rem;
            border: 1px solid var(--slate-200);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.2rem;
            border-bottom: 1px solid var(--slate-200);
        }

        .modal-body {
            padding: 1rem 1.2rem 1.2rem;
        }

        .history-block {
            border: 1px solid var(--slate-200);
            border-radius: 0.7rem;
            padding: 0.9rem;
            margin-bottom: 0.9rem;
            background: var(--slate-50);
        }

        .history-title {
            font-weight: 700;
            color: var(--slate-700);
            margin-bottom: 0.4rem;
        }

        .history-sub {
            font-size: 0.82rem;
            color: var(--slate-600);
            margin-bottom: 0.35rem;
        }

        .danger-modal-content {
            width: min(520px, 100%);
            border-radius: 1rem;
            padding: 0;
            overflow: hidden;
            border: 1px solid #fee2e2;
            box-shadow: 0 24px 60px rgba(127, 29, 29, 0.2);
        }

        .danger-modal-header {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 1.1rem;
            background: linear-gradient(135deg, #fff1f2, #fee2e2);
            border-bottom: 1px solid #fecaca;
        }

        .danger-modal-icon {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex: 0 0 auto;
        }

        .danger-modal-title {
            font-size: 1rem;
            font-weight: 700;
            color: #7f1d1d;
            margin: 0;
        }

        .danger-modal-body {
            padding: 1rem 1.1rem 0.9rem;
            color: var(--slate-700);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .danger-modal-project {
            margin-top: 0.4rem;
            padding: 0.55rem 0.65rem;
            border-radius: 0.55rem;
            border: 1px solid #fecaca;
            background: #fff1f2;
            color: #9f1239;
            font-size: 0.82rem;
            word-break: break-word;
        }

        .danger-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.6rem;
            padding: 0.95rem 1.1rem 1.1rem;
        }

        .btn-danger {
            border: 1px solid #dc2626;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }

        .btn-danger:hover {
            filter: brightness(0.98);
        }

        @media (max-width: 1000px) {
            .tracking-table th:nth-child(2),
            .tracking-table td:nth-child(2) {
                min-width: 260px;
            }

            .tracking-table th:last-child,
            .tracking-table td:last-child {
                width: 150px;
                min-width: 150px;
            }

            .tracking-table th:nth-child(6),
            .tracking-table td:nth-child(6) {
                min-width: 160px;
            }

            .tracking-table th:nth-child(7),
            .tracking-table td:nth-child(7) {
                min-width: 120px;
            }

            .project-info {
                grid-template-columns: 1fr;
            }

            .table-controls {
                justify-content: stretch;
                width: 100%;
            }

            .table-search-input {
                width: 100%;
            }

            .receipt-grid,
            .receipt-grid-2 {
                grid-template-columns: 1fr;
            }

            .quantity-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                justify-content: stretch;
            }

            .form-actions .btn {
                width: 100%;
            }
        }
    </style>

    <div class="tracking-page">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-warning">
                <strong>Terdapat kesalahan:</strong>
                <ul style="margin-left: 1rem; margin-top: 0.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="table-head">
                <h3 class="card-title" style="margin: 0;">List Project</h3>
                <div class="table-controls">
                    <input type="text" id="projectSearchInput" class="table-search-input" placeholder="Cari project, customer, model, part number...">
                    <button type="button" class="btn btn-secondary" onclick="searchProjects()">Search</button>
                    <a href="{{ route('tracking-documents.create', absolute: false) }}" class="btn btn-primary">+ New Project</a>
                </div>
            </div>

            <div class="table-wrap">
                <table class="tracking-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Informasi Project</th>
                            <th>Versi / Revisi</th>
                            <th>PIC Engineering</th>
                            <th>PIC Marketing</th>
                            <th>Status Dokumen</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($projects as $project)
                            @php
                                $latestRevision = $project->revisions->first();
                                $latestMarketingSubmission = $project->revisions
                                    ->flatMap(fn ($item) => $item->cogmSubmissions)
                                    ->sortByDesc('submitted_at')
                                    ->first();
                                $displayPicMarketing = $latestRevision?->pic_marketing ?: ($latestMarketingSubmission?->pic_marketing ?? null);
                            @endphp
                            @if(!$latestRevision)
                                @continue
                            @endif
                            <tr>
                                <td>{{ optional($latestRevision->received_date)->format('d/m/Y') ?: '-' }}</td>
                                <td>
                                    <div class="project-info">
                                        <div class="project-item">
                                            <span class="project-label">Business Categories</span>
                                            <span class="project-value">{{ optional($project->product)->code ? (optional($project->product)->code . ' - ' . optional($project->product)->name) : (optional($project->product)->name ?: '-') }}</span>
                                        </div>
                                        <div class="project-item">
                                            <span class="project-label">Customer</span>
                                            <span class="project-value">{{ $project->customer }}</span>
                                        </div>
                                        <div class="project-item">
                                            <span class="project-label">Model</span>
                                            <span class="project-value">{{ $project->model }}</span>
                                        </div>
                                        <div class="project-item">
                                            <span class="project-label">Part Number</span>
                                            <span class="project-value">{{ $project->part_number }}</span>
                                        </div>
                                        <div class="project-item">
                                            <span class="project-label">Part Name</span>
                                            <span class="project-value">{{ $project->part_name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $latestRevision->version_label }} ({{ max(0, $project->revisions->count() - 1) }} revisi)</td>
                                <td>{{ $latestRevision->pic_engineering }}</td>
                                <td>{{ $displayPicMarketing ?: '-' }}</td>
                                <td>
                                    <span
                                        class="status-badge {{ $latestRevision->status === \App\Models\DocumentRevision::STATUS_PENDING_FORM_INPUT ? 'status-pending' : ($latestRevision->status === \App\Models\DocumentRevision::STATUS_SUDAH_COSTING ? 'status-generated' : ($latestRevision->status === \App\Models\DocumentRevision::STATUS_PENDING_PRICING ? 'status-warning' : ($latestRevision->status === \App\Models\DocumentRevision::STATUS_COGM_GENERATED ? 'status-generated' : 'status-submitted'))) }}">
                                        {{ $latestRevision->status_label }}
                                    </span>
                                </td>
                                <td>{{ optional($latestRevision->updated_at)?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?: '-' }}</td>
                                <td>
                                    <div class="action-group">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            onclick="openEditProjectModal({{ $project->id }})">
                                            <svg class="action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                                            </svg>
                                            <span>Edit Info Project</span>
                                        </button>

                                        <button type="button" class="btn btn-secondary btn-sm"
                                            onclick="openHistoryModal({{ $project->id }})">
                                            <svg class="action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <path d="M12 7v5l3 2" />
                                            </svg>
                                            <span>View History</span>
                                        </button>

                                        <button type="button" class="btn btn-primary btn-sm"
                                            onclick="openSubmitModal({{ $latestRevision->id }})">
                                            <svg class="action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 2L11 13" />
                                                <path d="M22 2L15 22l-4-9-9-4 20-7z" />
                                            </svg>
                                            <span>Submit COGM</span>
                                        </button>

                                        <form action="{{ route('tracking-documents.destroy-project', ['project' => $project->id], absolute: false) }}" method="POST"
                                            onsubmit="return confirmDeleteProject(event, this);"
                                            data-project-label="{{ $project->customer }} / {{ $project->model }} / {{ $project->part_number }}"
                                            style="display: inline-flex;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-secondary btn-sm action-delete">
                                                <svg class="action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                    <path d="M10 10v7" />
                                                    <path d="M14 10v7" />
                                                </svg>
                                                <span>Hapus Semua</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--slate-500);">Belum ada dokumen tracking.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @foreach ($projects as $project)
        <div id="history-modal-{{ $project->id }}" class="modal is-hidden" onclick="handleOverlayClose(event, this.id)">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="card-title" style="margin: 0;">History Revisi - {{ $project->customer }} / {{ $project->model }}</h3>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('history-modal-{{ $project->id }}')">
                        Tutup
                    </button>
                </div>
                <div class="modal-body">
                    <div class="history-block" style="background: #eef2ff;">
                        <div class="history-title">Ringkasan History</div>
                        <div class="history-sub">Total revisi dari Engineering: {{ $project->revisions->count() }} kali</div>
                        <div class="history-sub">Total submit COGM ke Marketing: {{ $project->revisions->sum(fn ($item) => $item->cogmSubmissions->count()) }} kali</div>
                        @if($project->revisions->isNotEmpty())
                            <div class="action-group" style="margin-top: 0.6rem;">
                                <form action="{{ route('tracking-documents.add-version', ['revision' => $project->revisions->first()->id], absolute: false) }}" method="POST" style="display: inline-flex;"
                                    onsubmit="return confirmAddVersion(event, this);">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Tambah Versi</button>
                                </form>
                            </div>
                        @endif
                    </div>

                    @forelse ($project->revisions as $revision)
                        @php
                            $partlistUpdatedAt = $revision->partlist_updated_at ? $revision->partlist_updated_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') : null;
                            $umhUpdatedAt = $revision->umh_updated_at ? $revision->umh_updated_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') : null;
                            $remarkValue = trim((string) ($revision->change_remark ?? ''));
                            if ($remarkValue === '' || in_array($remarkValue, [
                                '-',
                                'Revisi Engineering diperbarui melalui update dokumen.',
                                'Revisi Engineering diterima. Versi baru dibuat.',
                                'Revisi Engineering diterima melalui update dokumen.',
                            ], true)) {
                                $remarkValue = '-';
                            }
                        @endphp
                        <div class="history-block">
                            <div class="history-title">{{ $revision->version_label }} - Diterima {{ optional($revision->received_date)->format('d/m/Y') ?: '-' }}</div>
                            <div class="history-sub">PIC Engineering: {{ $revision->pic_engineering }}</div>
                            <div class="history-sub">Status Terakhir: {{ $revision->status_label }}</div>
                            <div class="history-sub">
                                <strong>Nama Dokumen Partlist:</strong>
                                @if(!empty($revision->partlist_original_name) && !empty($revision->partlist_file_path))
                                    <a href="{{ route('tracking-documents.download', ['revision' => $revision->id, 'type' => 'partlist'], absolute: false) }}" target="_blank" rel="noopener noreferrer">{{ $revision->partlist_original_name }}</a>
                                    @if((int) ($revision->partlist_update_count ?? 0) > 0)
                                        <span style="font-size: 0.78rem; color: var(--slate-500);">({{ $revision->partlist_update_count }}x update{{ $partlistUpdatedAt ? ', ' . $partlistUpdatedAt : '' }})</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                            <div class="history-sub">
                                <strong>Nama Dokumen UMH:</strong>
                                @if(!empty($revision->umh_original_name) && !empty($revision->umh_file_path))
                                    <a href="{{ route('tracking-documents.download', ['revision' => $revision->id, 'type' => 'umh'], absolute: false) }}" target="_blank" rel="noopener noreferrer">{{ $revision->umh_original_name }}</a>
                                    @if((int) ($revision->umh_update_count ?? 0) > 0)
                                        <span style="font-size: 0.78rem; color: var(--slate-500);">({{ $revision->umh_update_count }}x update{{ $umhUpdatedAt ? ', ' . $umhUpdatedAt : '' }})</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                            <div class="history-sub"><strong>Remark Perubahan:</strong> {{ $remarkValue }}</div>
                            <div class="action-group" style="margin-bottom: 0.5rem; min-width: 0;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="openUpdateFilesModal({{ $revision->id }})">Update</button>
                                <form action="{{ route('tracking-documents.process-form-input', ['revision' => $revision->id], absolute: false) }}" method="POST" style="display: inline-flex;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Costing</button>
                                </form>
                                @if($project->revisions->count() > 1)
                                    <form action="{{ route('tracking-documents.delete-version', ['revision' => $revision->id], absolute: false) }}" method="POST"
                                        onsubmit="return confirmDeleteVersion(event, this);"
                                        data-version-label="{{ $revision->version_label }} - {{ $project->customer }} / {{ $project->model }} / {{ $project->part_number }}"
                                        style="display: inline-flex;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus Versi</button>
                                    </form>
                                @endif
                            </div>

                            @if ($revision->cogmSubmissions->isNotEmpty())
                                @foreach ($revision->cogmSubmissions->sortByDesc('submitted_at') as $submission)
                                    <div class="history-sub">
                                        Submit {{ optional($submission->submitted_at)?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?: '-' }}
                                        | PIC Marketing: {{ $submission->pic_marketing }}
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @empty
                        <p style="color: var(--slate-500);">Belum ada riwayat revisi.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach

    @foreach ($projects as $project)
        @php
            $editRevision = $project->revisions->first();
        @endphp
        <div id="edit-project-modal-{{ $project->id }}" class="modal is-hidden" onclick="handleOverlayClose(event, this.id)">
            <div class="modal-content" style="max-width: 1150px;">
                <div class="modal-header">
                    <h3 class="card-title" style="margin: 0;">Edit Informasi Project - {{ $project->part_number }}</h3>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('edit-project-modal-{{ $project->id }}')">
                        Tutup
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tracking-documents.update-project-info', ['project' => $project->id], absolute: false) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="receipt-grid project-edit-grid" style="margin-bottom: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Business Categories <span style="color: var(--red-500);">*</span></label>
                                <select name="business_category_id" class="form-select" required>
                                    <option value="">-- Pilih Business Categories --</option>
                                    @foreach($businessCategories as $businessCategory)
                                        @php
                                            $selectedBusinessCategory = false;
                                            if (!empty($project->product)) {
                                                $selectedBusinessCategory = trim((string) $project->product->code) === trim((string) $businessCategory->code)
                                                    || trim((string) $project->product->name) === trim((string) $businessCategory->name);
                                            }
                                        @endphp
                                        <option value="{{ $businessCategory->id }}" {{ $selectedBusinessCategory ? 'selected' : '' }}>
                                            {{ $businessCategory->code }} - {{ $businessCategory->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Customer <span style="color: var(--red-500);">*</span></label>
                                <select name="customer_id" class="form-select" required>
                                    <option value="">-- Pilih Customer --</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ trim(Str::lower($project->customer)) === trim(Str::lower($customer->name)) ? 'selected' : '' }}>
                                            {{ $customer->code }} - {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Model <span style="color: var(--red-500);">*</span></label>
                                <input type="text" name="model" class="form-input" value="{{ $project->model }}" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Part Number <span style="color: var(--red-500);">*</span></label>
                                <input type="text" name="part_number" class="form-input" value="{{ $project->part_number }}" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Part Name <span style="color: var(--red-500);">*</span></label>
                                <input type="text" name="part_name" class="form-input" value="{{ $project->part_name }}" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Quantity</label>
                                <div class="quantity-grid">
                                    <input type="number" name="forecast" class="form-input" min="0" value="2000" placeholder="2000">
                                    <select name="forecast_uom" class="form-select">
                                        <option value="PCE" selected>PCE</option>
                                        <option value="Set">Set</option>
                                    </select>
                                    <select name="forecast_basis" class="form-select">
                                        <option value="per_month" selected>Per Bulan</option>
                                        <option value="per_year">Per Tahun</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Product's Life</label>
                                <input type="number" name="project_period" class="form-input" min="0" value="2">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Plant</label>
                                <select name="line" class="form-select">
                                    <option value="">-- Pilih Plant --</option>
                                    @foreach($plants as $plant)
                                        <option value="{{ $plant->code }}" {{ trim((string) ($project->line ?? '')) === trim((string) $plant->code) ? 'selected' : '' }}>
                                            {{ $plant->code }} - {{ $plant->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Periode</label>
                                <select name="period" class="form-select">
                                    <option value="">-- Pilih Periode --</option>
                                    @foreach($periods as $period)
                                        <option value="{{ $period }}">{{ $period }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tanggal Diterima</label>
                                <input type="date" name="received_date" class="form-input"
                                    value="{{ optional($editRevision?->received_date)->format('Y-m-d') ?: now()->format('Y-m-d') }}">
                            </div>

                            <div class="form-group">
                                <label class="form-label">PIC Engineering <span style="color: var(--red-500);">*</span></label>
                                <select name="pic_engineering" class="form-select" required>
                                    <option value="">-- Pilih PIC Engineering --</option>
                                    @foreach($picsEngineering as $pic)
                                        <option value="{{ $pic->name }}" {{ ($editRevision?->pic_engineering === $pic->name) ? 'selected' : '' }}>{{ $pic->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">PIC Marketing <span style="color: var(--red-500);">*</span></label>
                                <select name="pic_marketing" class="form-select" required>
                                    <option value="">-- Pilih PIC Marketing --</option>
                                    @foreach($picsMarketing as $pic)
                                        <option value="{{ $pic->name }}" {{ ($editRevision?->pic_marketing === $pic->name) ? 'selected' : '' }}>{{ $pic->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">A00</label>
                                @php $editA00 = $editRevision?->a00 ?? 'belum_ada'; @endphp
                                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 0.35rem;">
                                    <label style="display: inline-flex; gap: 0.4rem; align-items: center; font-size: 0.82rem; color: var(--slate-700);">
                                        <input type="radio" name="a00" value="ada" {{ $editA00 === 'ada' ? 'checked' : '' }} onchange="toggleEditADocument({{ $project->id }}, 'a00')">
                                        Ada
                                    </label>
                                    <label style="display: inline-flex; gap: 0.4rem; align-items: center; font-size: 0.82rem; color: var(--slate-700);">
                                        <input type="radio" name="a00" value="belum_ada" {{ $editA00 !== 'ada' ? 'checked' : '' }} onchange="toggleEditADocument({{ $project->id }}, 'a00')">
                                        Belum ada
                                    </label>
                                </div>

                                <div id="editA00DocumentWrap-{{ $project->id }}" style="margin-top: 0.6rem; {{ $editA00 === 'ada' ? '' : 'display:none;' }}">
                                    <label class="form-label">Dokumen A00 (PDF)</label>
                                    <div class="file-input-wrap">
                                        <input type="file" name="a00_document_file" accept=".pdf"
                                            data-original-name="{{ $editRevision?->a00_document_original_name ?: '' }}"
                                            onchange="updateFileLabel(this, 'editA00DocumentFileName{{ $project->id }}')">
                                        <div class="file-input-content" aria-hidden="true">
                                            <span class="file-input-btn">Pilih File</span>
                                            <span id="editA00DocumentFileName{{ $project->id }}" class="file-input-name">{{ $editRevision?->a00_document_original_name ?: 'Belum ada file dipilih' }}</span>
                                        </div>
                                    </div>
                                    <small class="field-help">Format: .pdf, maksimal 10MB</small>
                                    @if(!empty($editRevision?->a00_document_file_path))
                                        <small class="field-help">
                                            <a href="{{ route('tracking-documents.download', ['revision' => $editRevision->id, 'type' => 'a00'], absolute: false) }}" target="_blank">Lihat Dokumen Lama</a>
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">A04</label>
                                @php $editA04 = $editRevision?->a04 ?? 'belum_ada'; @endphp
                                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 0.35rem;">
                                    <label style="display: inline-flex; gap: 0.4rem; align-items: center; font-size: 0.82rem; color: var(--slate-700);">
                                        <input type="radio" name="a04" value="ada" {{ $editA04 === 'ada' ? 'checked' : '' }} onchange="toggleEditADocument({{ $project->id }}, 'a04')">
                                        Ada
                                    </label>
                                    <label style="display: inline-flex; gap: 0.4rem; align-items: center; font-size: 0.82rem; color: var(--slate-700);">
                                        <input type="radio" name="a04" value="belum_ada" {{ $editA04 !== 'ada' ? 'checked' : '' }} onchange="toggleEditADocument({{ $project->id }}, 'a04')">
                                        Belum ada
                                    </label>
                                </div>

                                <div id="editA04DocumentWrap-{{ $project->id }}" style="margin-top: 0.6rem; {{ $editA04 === 'ada' ? '' : 'display:none;' }}">
                                    <label class="form-label">Dokumen A04 (PDF)</label>
                                    <div class="file-input-wrap">
                                        <input type="file" name="a04_document_file" accept=".pdf"
                                            data-original-name="{{ $editRevision?->a04_document_original_name ?: '' }}"
                                            onchange="updateFileLabel(this, 'editA04DocumentFileName{{ $project->id }}')">
                                        <div class="file-input-content" aria-hidden="true">
                                            <span class="file-input-btn">Pilih File</span>
                                            <span id="editA04DocumentFileName{{ $project->id }}" class="file-input-name">{{ $editRevision?->a04_document_original_name ?: 'Belum ada file dipilih' }}</span>
                                        </div>
                                    </div>
                                    <small class="field-help">Format: .pdf, maksimal 10MB</small>
                                    @if(!empty($editRevision?->a04_document_file_path))
                                        <small class="field-help">
                                            <a href="{{ route('tracking-documents.download', ['revision' => $editRevision->id, 'type' => 'a04'], absolute: false) }}" target="_blank">Lihat Dokumen Lama</a>
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">A05</label>
                                @php $editA05 = $editRevision?->a05 ?? 'belum_ada'; @endphp
                                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 0.35rem;">
                                    <label style="display: inline-flex; gap: 0.4rem; align-items: center; font-size: 0.82rem; color: var(--slate-700);">
                                        <input type="radio" name="a05" value="ada" {{ $editA05 === 'ada' ? 'checked' : '' }} onchange="toggleEditADocument({{ $project->id }}, 'a05')">
                                        Ada
                                    </label>
                                    <label style="display: inline-flex; gap: 0.4rem; align-items: center; font-size: 0.82rem; color: var(--slate-700);">
                                        <input type="radio" name="a05" value="belum_ada" {{ $editA05 !== 'ada' ? 'checked' : '' }} onchange="toggleEditADocument({{ $project->id }}, 'a05')">
                                        Belum ada
                                    </label>
                                </div>

                                <div id="editA05DocumentWrap-{{ $project->id }}" style="margin-top: 0.6rem; {{ $editA05 === 'ada' ? '' : 'display:none;' }}">
                                    <label class="form-label">Dokumen A05 (PDF)</label>
                                    <div class="file-input-wrap">
                                        <input type="file" name="a05_document_file" accept=".pdf"
                                            data-original-name="{{ $editRevision?->a05_document_original_name ?: '' }}"
                                            onchange="updateFileLabel(this, 'editA05DocumentFileName{{ $project->id }}')">
                                        <div class="file-input-content" aria-hidden="true">
                                            <span class="file-input-btn">Pilih File</span>
                                            <span id="editA05DocumentFileName{{ $project->id }}" class="file-input-name">{{ $editRevision?->a05_document_original_name ?: 'Belum ada file dipilih' }}</span>
                                        </div>
                                    </div>
                                    <small class="field-help">Format: .pdf, maksimal 10MB</small>
                                    @if(!empty($editRevision?->a05_document_file_path))
                                        <small class="field-help">
                                            <a href="{{ route('tracking-documents.download', ['revision' => $editRevision->id, 'type' => 'a05'], absolute: false) }}" target="_blank">Lihat Dokumen Lama</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Simpan Informasi Project</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    @foreach ($revisions as $revision)
        <div id="update-files-modal-{{ $revision->id }}" class="modal is-hidden" onclick="handleOverlayClose(event, this.id)">
            <div class="modal-content" style="max-width: 700px;">
                <div class="modal-header">
                    <h3 class="card-title" style="margin: 0;">Update Dokumen - {{ $revision->project->part_number }} {{ $revision->version_label }}</h3>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('update-files-modal-{{ $revision->id }}')">
                        Tutup
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tracking-documents.update-files', ['revision' => $revision->id], absolute: false) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">Partlist (Excel)</label>
                            <div class="file-input-wrap">
                                <input type="file" name="partlist_file" accept=".xls,.xlsx"
                                    onchange="updateFileLabel(this, 'updatePartlistFileName{{ $revision->id }}')">
                                <div class="file-input-content" aria-hidden="true">
                                    <span class="file-input-btn">Pilih File</span>
                                    <span id="updatePartlistFileName{{ $revision->id }}" class="file-input-name">Belum ada file dipilih</span>
                                </div>
                            </div>
                            <small class="field-help">Kosongkan jika tidak ingin mengganti file Partlist.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">UMH (Excel)</label>
                            <div class="file-input-wrap">
                                <input type="file" name="umh_file" accept=".xls,.xlsx"
                                    onchange="updateFileLabel(this, 'updateUmhFileName{{ $revision->id }}')">
                                <div class="file-input-content" aria-hidden="true">
                                    <span class="file-input-btn">Pilih File</span>
                                    <span id="updateUmhFileName{{ $revision->id }}" class="file-input-name">Belum ada file dipilih</span>
                                </div>
                            </div>
                            <small class="field-help">Kosongkan jika tidak ingin mengganti file UMH.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">Remark Perubahan</label>
                            <textarea name="change_remark" class="form-input" rows="2"
                                placeholder="Contoh: update qty part A, ganti material part B"></textarea>
                            <small class="field-help">Jika disimpan, sistem akan memperbarui revisi yang dipilih.</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Simpan Update Dokumen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="submit-modal-{{ $revision->id }}" class="modal is-hidden" onclick="handleOverlayClose(event, this.id)">
            <div class="modal-content" style="max-width: 640px;">
                <div class="modal-header">
                    <h3 class="card-title" style="margin: 0;">Submit COGM - {{ $revision->project->part_number }} {{ $revision->version_label }}</h3>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('submit-modal-{{ $revision->id }}')">
                        Tutup
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tracking-documents.submit-cogm', ['revision' => $revision->id], absolute: false) }}" method="POST">
                        @csrf
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">PIC Marketing <span style="color: var(--red-500);">*</span></label>
                            <select name="pic_marketing" class="form-select" required>
                                <option value="">-- Pilih PIC Marketing --</option>
                                @foreach($picsMarketing as $pic)
                                    <option value="{{ $pic->name }}">{{ $pic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">Nilai COGM</label>
                            <input type="number" step="0.01" name="cogm_value" class="form-input" placeholder="Contoh: 125000.50">
                        </div>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">Submitted By</label>
                            <input type="text" name="submitted_by" class="form-input" placeholder="Nama tim costing">
                        </div>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-input" rows="3"></textarea>
                        </div>
                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary">Submit ke Marketing</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <div id="delete-confirm-modal" class="modal is-hidden" onclick="handleOverlayClose(event, this.id)">
        <div class="modal-content danger-modal-content">
            <div class="danger-modal-header">
                <div class="danger-modal-icon">!</div>
                <h4 class="danger-modal-title">Konfirmasi Hapus Semua Data</h4>
            </div>
            <div class="danger-modal-body">
                Tindakan ini akan menghapus semua revisi, submit COGM, dan file dokumen terkait secara permanen.
                <div class="danger-modal-project" id="deleteConfirmProjectLabel">-</div>
            </div>
            <div class="danger-modal-actions">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeDeleteConfirmModal()">Batal</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="submitDeleteProject()">Ya, Hapus Semua</button>
            </div>
        </div>
    </div>

    <div id="delete-version-confirm-modal" class="modal is-hidden" onclick="handleOverlayClose(event, this.id)">
        <div class="modal-content danger-modal-content">
            <div class="danger-modal-header">
                <div class="danger-modal-icon">!</div>
                <h4 class="danger-modal-title">Konfirmasi Hapus Versi</h4>
            </div>
            <div class="danger-modal-body">
                Tindakan ini akan menghapus versi yang dipilih beserta data turunannya pada versi tersebut.
                <div class="danger-modal-project" id="deleteVersionConfirmLabel">-</div>
            </div>
            <div class="danger-modal-actions">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeDeleteVersionConfirmModal()">Batal</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="submitDeleteVersion()">Ya, Hapus Versi</button>
            </div>
        </div>
    </div>

    <div id="add-version-confirm-modal" class="modal is-hidden" onclick="handleOverlayClose(event, this.id)">
        <div class="modal-content" style="width: min(480px, 100%); padding: 1.5rem;">
            <div style="text-align: center; margin-bottom: 1rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" style="width: 48px; height: 48px; margin: 0 auto;">
                    <path d="M12 9v4" /><path d="M12 17h.01" />
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                </svg>
                <h4 style="margin: 0.8rem 0 0.4rem;">Konfirmasi Tambah Versi</h4>
                <p style="color: var(--slate-600, #555); font-size: 0.9rem;">Tambah versi baru untuk project ini? Aksi ini akan membuat revisi baru.</p>
            </div>
            <div style="display: flex; justify-content: center; gap: 0.6rem;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAddVersionConfirmModal()">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="submitAddVersion()">Ya, Tambah Versi</button>
            </div>
        </div>
    </div>

    <script>
        let pendingDeleteForm = null;
        let pendingDeleteVersionForm = null;
        let pendingAddVersionForm = null;

        function searchProjects() {
            const input = document.getElementById('projectSearchInput');
            const keyword = (input?.value || '').trim().toLowerCase();
            const tbody = document.querySelector('.tracking-table tbody');

            if (!tbody) {
                return;
            }

            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.forEach(function (row) {
                const isEmptyRow = row.children.length === 1 && row.children[0].hasAttribute('colspan');
                if (isEmptyRow) {
                    row.style.display = '';
                    return;
                }

                const text = (row.innerText || row.textContent || '').toLowerCase();
                const matched = keyword === '' || text.includes(keyword);
                row.style.display = matched ? '' : 'none';
            });
        }

        function openHistoryModal(projectId) {
            openModal('history-modal-' + projectId);
        }

        function openSubmitModal(revisionId) {
            openModal('submit-modal-' + revisionId);
        }

        function openUpdateFilesModal(revisionId) {
            openModal('update-files-modal-' + revisionId);
        }

        function openEditProjectModal(projectId) {
            openModal('edit-project-modal-' + projectId);
            toggleEditADocument(projectId, 'a00');
            toggleEditADocument(projectId, 'a04');
            toggleEditADocument(projectId, 'a05');
        }

        function toggleEditADocument(projectId, prefix) {
            const selected = document.querySelector('#edit-project-modal-' + projectId + ' input[name="' + prefix + '"]:checked');
            const wrapper = document.getElementById('edit' + prefix.toUpperCase() + 'DocumentWrap-' + projectId);
            const input = document.querySelector('#edit-project-modal-' + projectId + ' input[name="' + prefix + '_document_file"]');
            const labelId = 'edit' + prefix.toUpperCase() + 'DocumentFileName' + projectId;

            if (!wrapper || !input) {
                return;
            }

            const isAda = selected && selected.value === 'ada';
            wrapper.style.display = isAda ? '' : 'none';
            input.required = !!isAda;

            if (!isAda) {
                input.value = '';
                const label = document.getElementById(labelId);
                if (label) {
                    label.textContent = 'Belum ada file dipilih';
                }
                return;
            }

            if (!input.files || input.files.length === 0) {
                const label = document.getElementById(labelId);
                if (label) {
                    label.textContent = input.dataset.originalName || 'Belum ada file dipilih';
                }
            }
        }

        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('is-hidden');
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('is-hidden');
            }
        }

        function handleOverlayClose(event, id) {
            if (event.target && event.target.id === id) {
                if (id === 'delete-confirm-modal') {
                    closeDeleteConfirmModal();
                    return;
                }
                if (id === 'delete-version-confirm-modal') {
                    closeDeleteVersionConfirmModal();
                    return;
                }
                if (id === 'add-version-confirm-modal') {
                    closeAddVersionConfirmModal();
                    return;
                }
                closeModal(id);
            }
        }

        function confirmDeleteProject(event, form) {
            event.preventDefault();
            pendingDeleteForm = form;

            const label = document.getElementById('deleteConfirmProjectLabel');
            if (label) {
                label.textContent = form.getAttribute('data-project-label') || '-';
            }

            openModal('delete-confirm-modal');
            return false;
        }

        function closeDeleteConfirmModal() {
            pendingDeleteForm = null;
            closeModal('delete-confirm-modal');
        }

        function submitDeleteProject() {
            if (!pendingDeleteForm) {
                closeModal('delete-confirm-modal');
                return;
            }

            const formToSubmit = pendingDeleteForm;
            pendingDeleteForm = null;
            closeModal('delete-confirm-modal');
            formToSubmit.submit();
        }

        function confirmDeleteVersion(event, form) {
            event.preventDefault();
            pendingDeleteVersionForm = form;

            const label = document.getElementById('deleteVersionConfirmLabel');
            if (label) {
                label.textContent = form.getAttribute('data-version-label') || '-';
            }

            openModal('delete-version-confirm-modal');
            return false;
        }

        function closeDeleteVersionConfirmModal() {
            pendingDeleteVersionForm = null;
            closeModal('delete-version-confirm-modal');
        }

        function submitDeleteVersion() {
            if (!pendingDeleteVersionForm) {
                closeModal('delete-version-confirm-modal');
                return;
            }

            const formToSubmit = pendingDeleteVersionForm;
            pendingDeleteVersionForm = null;
            closeModal('delete-version-confirm-modal');
            formToSubmit.submit();
        }

        function confirmAddVersion(event, form) {
            event.preventDefault();
            pendingAddVersionForm = form;
            openModal('add-version-confirm-modal');
            return false;
        }

        function closeAddVersionConfirmModal() {
            pendingAddVersionForm = null;
            closeModal('add-version-confirm-modal');
        }

        function submitAddVersion() {
            if (!pendingAddVersionForm) {
                closeModal('add-version-confirm-modal');
                return;
            }

            const formToSubmit = pendingAddVersionForm;
            pendingAddVersionForm = null;
            closeModal('add-version-confirm-modal');
            formToSubmit.submit();
        }

        function updateFileLabel(input, labelId) {
            const label = document.getElementById(labelId);
            if (!label) {
                return;
            }

            if (input.files && input.files.length > 0) {
                label.textContent = input.files[0].name;
                return;
            }

            label.textContent = 'Belum ada file dipilih';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('projectSearchInput');

            if (searchInput) {
                searchInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        searchProjects();
                    }
                });
            }

            document.querySelectorAll('[id^="edit-project-modal-"]').forEach(function (modal) {
                const projectId = modal.id.replace('edit-project-modal-', '');
                if (projectId) {
                    toggleEditADocument(projectId, 'a00');
                    toggleEditADocument(projectId, 'a04');
                    toggleEditADocument(projectId, 'a05');
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    const deleteModal = document.getElementById('delete-confirm-modal');
                    if (deleteModal && !deleteModal.classList.contains('is-hidden')) {
                        closeDeleteConfirmModal();
                        return;
                    }

                    const deleteVersionModal = document.getElementById('delete-version-confirm-modal');
                    if (deleteVersionModal && !deleteVersionModal.classList.contains('is-hidden')) {
                        closeDeleteVersionConfirmModal();
                    }
                }
            });
        });
    </script>
@endsection
