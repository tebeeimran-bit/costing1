@extends('layouts.app')

@section('title', 'Database Cycle Time')
@section('page-title', 'Database Cycle Time Template')

@section('breadcrumb')
    <a href="{{ route('database.products', absolute: false) }}">Database</a>
    <span class="breadcrumb-separator">/</span>
    <span>Cycle Time</span>
@endsection

@section('content')
    @if(session('success'))
        <div
            style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #a7f3d0;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div
            style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #fecaca;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-header">
            <h3 class="card-title">Tambah Process Template</h3>
        </div>

        <form action="{{ route('database.cycle-time-templates.store', absolute: false) }}" method="POST"
            style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: end;">
            @csrf
            <div style="flex: 1; min-width: 260px;">
                <label class="form-label">Process</label>
                <input type="text" name="process" class="form-input" value="{{ old('process') }}"
                    placeholder="Contoh: Cutting, Stripping" required>
            </div>
            <button type="submit" class="btn btn-primary">Tambah</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Process Cycle Time</h3>
        </div>

        <div class="material-table-container" style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">No.</th>
                        <th>Process</th>
                        <th style="width: 120px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $index => $template)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $template->process }}</td>
                            <td style="text-align: center;">
                                <form
                                    action="{{ route('database.cycle-time-templates.destroy', $template->id, false) }}"
                                    method="POST" style="display: inline;"
                                    onsubmit="return confirm('Hapus process template ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Hapus">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            style="width: 16px; height: 16px;">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center;">Belum ada process template.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-delete:hover {
            background: #fecaca;
        }
    </style>
@endsection
