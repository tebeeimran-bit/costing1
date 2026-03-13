@extends('layouts.app')

@section('title', 'Database Product')
@section('page-title', 'Database Product')

@section('breadcrumb')
    <a href="{{ route('database.products', absolute: false) }}">Database</a>
    <span class="breadcrumb-separator">/</span>
    <span>Products</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Produk</h3>
        </div>
        <div class="material-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Produk</th>
                        <th>Dibuat Pada</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->created_at ? $product->created_at->format('d M Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center;">Tidak ada produk ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection