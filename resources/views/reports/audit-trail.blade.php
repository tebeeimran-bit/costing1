@extends('layouts.app')
@section('title', 'Audit Trail')
@section('page-title', 'Audit Trail / Log Aktivitas')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Menu Utama</a>
    <span class="breadcrumb-separator">/</span>
    <span>Audit Trail</span>
@endsection

@section('content')
<style>
    .audit-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .audit-table th { background: var(--blue-600); color: #fff; padding: 0.6rem 0.75rem; text-align: left; }
    .audit-table td { padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--slate-200); }
    .audit-table tr:hover { background: #f8fafc; }
    .action-badge { display: inline-block; padding: 0.12rem 0.5rem; border-radius: 9999px; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; }
    .action-create { background: #d1fae5; color: #065f46; }
    .action-update { background: #dbeafe; color: #1d4ed8; }
    .action-delete { background: #fee2e2; color: #991b1b; }
    .action-status_change { background: #fef3c7; color: #92400e; }
    .action-export { background: #ede9fe; color: #5b21b6; }
    .action-login { background: #f1f5f9; color: #475569; }
    .module-badge { display: inline-block; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600; background: #f1f5f9; color: var(--slate-600); }
</style>

<div class="card" style="margin-bottom:1rem;">
    <div style="padding: 0.75rem 1rem;">
        <form method="GET" action="{{ route('audit-trail') }}" style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <select name="action" class="form-select" style="padding:0.45rem 0.75rem; font-size:0.85rem;">
                <option value="">Semua Action</option>
                @foreach($actionOptions as $opt)
                    <option value="{{ $opt }}" {{ request('action') === $opt ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $opt)) }}</option>
                @endforeach
            </select>
            <select name="module" class="form-select" style="padding:0.45rem 0.75rem; font-size:0.85rem;">
                <option value="">Semua Module</option>
                @foreach($moduleOptions as $opt)
                    <option value="{{ $opt }}" {{ request('module') === $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary" style="padding:0.45rem 0.75rem; font-size:0.85rem;">Filter</button>
            @if(request('action') || request('module'))
                <a href="{{ route('audit-trail') }}" class="btn btn-secondary" style="padding:0.45rem 0.75rem; font-size:0.85rem;">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Log Aktivitas</h3>
        <span style="font-size:0.8rem; color:var(--slate-500);">{{ $logs->total() }} total log</span>
    </div>
    <div class="material-table-container">
        <table class="audit-table" style="min-width: 900px;">
            <thead>
                <tr><th style="width:160px;">Waktu</th><th>User</th><th style="width:100px;">Action</th><th style="width:90px;">Module</th><th>Target</th><th>Deskripsi</th><th style="width:110px;">IP Address</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="white-space:nowrap;">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td><strong>{{ $log->user_name }}</strong></td>
                    <td><span class="action-badge action-{{ $log->action }}">{{ str_replace('_', ' ', $log->action) }}</span></td>
                    <td><span class="module-badge">{{ $log->module }}</span></td>
                    <td>{{ $log->target ?? '-' }}</td>
                    <td style="max-width:250px;">{{ $log->description ?? '-' }}</td>
                    <td style="font-family:monospace; font-size:0.75rem; color:var(--slate-500);">{{ $log->ip_address ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; color:var(--slate-400); padding:2rem;">Belum ada log aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div style="padding: 1rem; border-top: 1px solid var(--slate-200); display:flex; justify-content:space-between; align-items:center;">
        <span style="font-size:0.82rem; color:var(--slate-500);">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</span>
        <div class="doc-pagination">{{ $logs->links('pagination.doc-paginator') }}</div>
    </div>
    @endif
</div>
@endsection
