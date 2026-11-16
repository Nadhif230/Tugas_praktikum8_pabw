@extends('layouts.app')

@section('title', 'Koleksi | SiPustaka')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">Katalog perpustakaan</div>
            <h1>Koleksi buku</h1>
            <p class="subtitle">Cari dan jelajahi buku yang tersedia untuk dipinjam.</p>
        </div>
        @if (auth()->user()->role === 'admin')
            <a class="button" href="{{ route('buku.create') }}">+ Tambah buku</a>
        @endif
    </div>

    @if (session('success'))<div class="alert">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

    <section class="card">
        <form class="search-form" method="GET" action="{{ route('buku.index') }}">
            <input class="search-input" type="search" name="search" value="{{ $search }}" placeholder="Cari judul, penulis, atau penerbit...">
            <button class="button" type="submit">Cari buku</button>
            @if ($search !== '')
                <a class="button button-secondary" href="{{ route('buku.index') }}">Reset</a>
            @endif
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Judul buku</th><th>Penulis</th><th>Penerbit</th><th>Tahun</th><th>Stok</th>@if (auth()->user()->role === 'admin')<th>Aksi</th>@endif</tr></thead>
                <tbody>
                    @forelse ($bukus as $buku)
                        <tr>
                            <td><strong>{{ $buku->judul }}</strong></td>
                            <td>{{ $buku->penulis }}</td>
                            <td>{{ $buku->penerbit }}</td>
                            <td>{{ $buku->tahun_terbit }}</td>
                            <td><span class="badge {{ $buku->stok < 1 ? 'badge-warning' : '' }}">{{ $buku->stok }} tersedia</span></td>
                            @if (auth()->user()->role === 'admin')
                                <td><div class="table-actions">
                                    <a class="button button-secondary button-small" href="{{ route('buku.edit', $buku) }}">Edit</a>
                                    <form class="inline-form" action="{{ route('buku.destroy', $buku) }}" method="POST" onsubmit="return confirm('Hapus buku ini?')">
                                        @csrf @method('DELETE')
                                        <button class="button button-danger button-small" type="submit">Hapus</button>
                                    </form>
                                </div></td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ auth()->user()->role === 'admin' ? 6 : 5 }}"><div class="empty-state"><strong>{{ $search ? 'Buku tidak ditemukan' : 'Belum ada buku' }}</strong>Coba kata kunci lain atau tambahkan koleksi baru.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $bukus->links() }}</div>
    </section>
@endsection