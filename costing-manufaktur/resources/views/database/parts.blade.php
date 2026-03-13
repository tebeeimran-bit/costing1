@extends('layouts.app')

@section('title', 'Database Part')
@section('page-title', 'Database Part (Material)')

@section('breadcrumb')
    <a href="{{ route('database.products', absolute: false) }}">Database</a>
    <span class="breadcrumb-separator">/</span>
    <span>Parts</span>
@endsection

@section('content')
    @if(session('success'))
        <div
            style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #a7f3d0;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
        <a href="{{ route('database.parts.create', absolute: false) }}" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Tambah Material
        </a>
    </div>

    <div class="material-table-container" style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="vertical-align: middle;">No.</th>
                    <th rowspan="2" style="vertical-align: middle;">Plant</th>
                    <th rowspan="2" style="vertical-align: middle;">Material (ID Code)</th>
                    <th rowspan="2" style="vertical-align: middle;">Material Description</th>
                    <th rowspan="2" style="vertical-align: middle;">Material Type</th>
                    <th rowspan="2" style="vertical-align: middle;">Material Group</th>
                    <th rowspan="2" style="vertical-align: middle;">Base UoM</th>
                    <th colspan="9" style="text-align: center;">Price
                    </th>
                    <th rowspan="2" style="vertical-align: middle;">Aksi</th>
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
            <tbody>
                @forelse($materials as $index => $material)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $material->plant ?? '-' }}</td>
                        <td>{{ $material->material_code ?? '-' }}</td>
                        <td>{{ $material->material_description ?? '-' }}</td>
                        <td>{{ $material->material_type ?? '-' }}</td>
                        <td>{{ $material->material_group ?? '-' }}</td>
                        <td>{{ $material->base_uom ?? '-' }}</td>
                        <td>{{ $material->price ? number_format($material->price, 0, ',', '.') : '0' }}</td>
                        <td>{{ $material->purchase_unit ?? '-' }}</td>
                        <td>{{ $material->currency ?? '-' }}</td>
                        <td>{{ $material->moq ? number_format($material->moq, 0, ',', '.') : '-' }}</td>
                        <td>{{ $material->cn ?? '-' }}</td>
                        <td>{{ $material->maker ?? '-' }}</td>
                        <td>{{ $material->add_cost_import_tax ? number_format($material->add_cost_import_tax, 2) . '%' : '-' }}
                        </td>
                        <td>{{ $material->price_update ? $material->price_update->format('d M Y') : '-' }}</td>
                        <td>{{ $material->price_before ? number_format($material->price_before, 0, ',', '.') : '-' }}</td>
                        <td style="white-space: nowrap;">
                            <a href="{{ route('database.parts.edit', $material->id, false) }}" class="btn-action btn-edit"
                                title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    style="width: 16px; height: 16px;">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                            </a>
                            <form action="{{ route('database.parts.destroy', $material->id, false) }}" method="POST"
                                style="display: inline;"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus material ini?');">
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
                        <td colspan="17" style="text-align: center;">Tidak ada material ditemukan</td>
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