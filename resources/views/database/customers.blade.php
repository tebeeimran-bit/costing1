@extends('layouts.app')

@section('title', 'Database Customer')
@section('page-title', 'Database Customer')

@section('breadcrumb')
    <a href="{{ route('database.products', absolute: false) }}">Database</a>
    <span class="breadcrumb-separator">/</span>
    <span>Customers</span>
@endsection

@section('content')
    @if(session('success'))
        <div
            style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #a7f3d0;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
        <a href="#" class="btn-primary" onclick="alert('Fitur tambah customer belum tersedia'); return false;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Tambah Customer
        </a>
    </div>

    <div class="material-table-container" style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No.</th>
                    <th>Code Customer</th>
                    <th>Nama Customer</th>
                    <th style="width: 100px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $index => $customer)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $customer->code }}</td>
                        <td>{{ $customer->name }}</td>
                        <td style="text-align: center;">
                            <button class="btn-action btn-edit" title="Edit" onclick="alert('Fitur edit belum tersedia')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    style="width: 16px; height: 16px;">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                            </button>
                            <button class="btn-action btn-delete" title="Hapus" onclick="alert('Fitur hapus belum tersedia')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    style="width: 16px; height: 16px;">
                                    <polyline points="3 6 5 6 21 6" />
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2-2v2" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">Tidak ada customer ditemukan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <style>
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--blue-700) 0%, var(--blue-800) 100%);
            transform: translateY(-2px);
        }

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

        .btn-edit {
            background: var(--blue-100);
            color: var(--blue-600);
        }

        .btn-edit:hover {
            background: var(--blue-200);
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