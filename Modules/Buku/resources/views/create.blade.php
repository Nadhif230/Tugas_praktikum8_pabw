@extends('layouts.app')

@section('title', 'Tambah Koleksi | SiPustaka')

@section('content')
    <div class="page-heading">
        <div><div class="eyebrow">Katalog perpustakaan</div><h1>Tambah buku</h1><p class="subtitle">Lengkapi detail buku yang akan ditambahkan ke koleksi.</p></div>
        <a class="button button-secondary" href="{{ route('buku.index') }}">Kembali ke koleksi</a>
    </div>
    <section class="card">
        @if ($errors->any())<div class="alert alert-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form action="{{ route('buku.store') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="field"><label for="judul">Judul buku</label><input id="judul" name="judul" value="{{ old('judul') }}" placeholder="Contoh: Laskar Pelangi" required></div>
                <div class="field"><label for="penulis">Penulis</label><input id="penulis" name="penulis" value="{{ old('penulis') }}" placeholder="Nama penulis" required></div>
                <div class="field"><label for="penerbit">Penerbit</label><input id="penerbit" name="penerbit" value="{{ old('penerbit') }}" placeholder="Nama penerbit" required></div>
                <div class="field"><label for="tahun_terbit">Tahun terbit</label><input id="tahun_terbit" type="number" name="tahun_terbit" value="{{ old('tahun_terbit') }}" min="1000" max="{{ date('Y') }}" required></div>
                <div class="field"><label for="stok">Jumlah stok</label><input id="stok" type="number" name="stok" value="{{ old('stok', 1) }}" min="0" required></div>
            </div>
            <div class="form-actions"><button class="button" type="submit">Simpan buku</button><a class="button button-quiet" href="{{ route('buku.index') }}">Batal</a></div>
        </form>
    </section>
@endsection