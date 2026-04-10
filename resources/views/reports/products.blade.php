@extends('layouts.app')
@section('title', 'Master Product')
@section('page-title', 'Master Product')
@section('breadcrumb')
    <a href="{{ route('database.parts') }}">Database</a>
    <span class="breadcrumb-separator">/</span>
    <span>Product</span>
@endsection

@section('content')
<style>
    .prod-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .prod-table th { background: var(--blue-600); color: #fff; padding: 0.6rem 0.75rem; text-align: left; }
    .prod-table td { padding: 0.55rem 0.75rem; border-bottom: 1px solid var(--slate-200); }
    .prod-table tr:hover { background: #f8fafc; }
    .line-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; }
</style>

@if(session('success'))
<div style="background: #d1fae5; color: #065f46; padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #a7f3d0;">{{ session('success') }}</div>
@endif

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h3 class="card-title">Tambah Product</h3></div>
    <div style="padding: 1rem;">
        <form method="POST" action="{{ route('products.store') }}" style="display: grid; grid-template-columns: 1fr 2fr 2fr auto; gap: 0.75rem; align-items: end;">
            @csrf
            <div><label style="display:block;font-size:0.75rem;font-weight:600;color:var(--slate-600);margin-bottom:0.3rem;">Code</label><input type="text" name="code" class="form-input" placeholder="WH-001" required></div>
            <div><label style="display:block;font-size:0.75rem;font-weight:600;color:var(--slate-600);margin-bottom:0.3rem;">Name</label><input type="text" name="name" class="form-input" placeholder="Wiring Harness" required></div>
            <div><label style="display:block;font-size:0.75rem;font-weight:600;color:var(--slate-600);margin-bottom:0.3rem;">Line / Category</label><input type="text" name="line" class="form-input" placeholder="WIRING HARNESS"></div>
            <button type="submit" class="btn btn-primary" style="height:fit-content;">Simpan</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Product ({{ $products->count() }})</h3></div>
    <div class="material-table-container">
        <table class="prod-table">
            <thead><tr><th>No.</th><th>Code</th><th>Name</th><th>Line / Category</th><th style="text-align:center;">Costing Data</th><th style="width:120px;text-align:center;">Aksi</th></tr></thead>
            <tbody>
                @forelse($products as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $p->code }}</strong></td>
                    <td>{{ $p->name }}</td>
                    <td>
                        @if($p->line)
                        <span class="line-badge" style="background: #dbeafe; color: #1d4ed8;">{{ $p->line }}</span>
                        @else
                        <span style="color:var(--slate-400);">-</span>
                        @endif
                    </td>
                    <td style="text-align:center;"><span style="background:#eff6ff; padding: 0.15rem 0.5rem; border-radius:9999px; font-size:0.75rem; font-weight:700; color:#2563eb;">{{ $p->costing_data_count }}</span></td>
                    <td style="text-align:center;">
                        <form method="POST" action="{{ route('products.destroy', $p->id) }}" onsubmit="return confirm('Hapus product ini?')" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" style="border:0;background:#fee2e2;color:#dc2626;border-radius:6px;padding:0.3rem;cursor:pointer;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--slate-400);padding:2rem;">Belum ada product.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
