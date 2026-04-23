@extends('layouts.app')

@section('title', 'Informasi Project')
@section('page-title', 'Informasi Project')

@section('breadcrumb')
    <a href="{{ route('database.parts', absolute: false) }}">Database</a>
    <span class="breadcrumb-separator">/</span>
    <span>Informasi Project</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Upload Dokumen Project</h3>
        </div>

        <div style="padding: 1.5rem;">
            @if(session('success'))
                <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #a7f3d0;">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('database.project-information.upload', absolute: false) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="display: grid; gap: 1.5rem; max-width: 600px;">
                    
                    <div class="form-group">
                        <label class="form-label">Nama Project / Assymbly No</label>
                        <input type="text" name="project_name" class="form-input" placeholder="Masukkan nama project atau Assy No" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dokumen A00 (Isian / Upload)</label>
                        <input type="file" name="document_a00" class="form-input" accept=".pdf,.doc,.docx,.xls,.xlsx">
                        <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.25rem;">Format yang didukung: PDF, Word, Excel.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dokumen A04 (Isian / Upload)</label>
                        <input type="file" name="document_a04" class="form-input" accept=".pdf,.doc,.docx,.xls,.xlsx">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dokumen A05 (Isian / Upload)</label>
                        <input type="file" name="document_a05" class="form-input" accept=".pdf,.doc,.docx,.xls,.xlsx">
                    </div>

                    <div style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 0.5rem; display: inline;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            Upload Dokumen
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
