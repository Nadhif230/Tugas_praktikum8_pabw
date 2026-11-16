@extends('layouts.app')

@section('title', 'Edit Koleksi | SiPustaka')

@section('content')
    <div class="page-heading">
        <div><div class="eyebrow">Katalog perpustakaan</div><h1>Edit buku</h1><p class="subtitle">Perbarui informasi “{{ $buku->judul }}”.</p></div>
        <a class="button button-secondary" href="{{ route('buku.index') }}">Kembali ke koleksi</a>
    </div>
    <section class="card">
        @if ($errors->any())<div class="alert alert-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form action="{{ route('buku.update', $buku) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="field"><label for="judul">Judul buku</label><input id="judul" name="judul" value="{{ old('judul', $buku->judul) }}" required></div>
                <div class="field"><label for="penulis">Penulis</label><input id="penulis" name="penulis" value="{{ old('penulis', $buku->penulis) }}" required></div>
                <div class="field"><label for="penerbit">Penerbit</label><input id="penerbit" name="penerbit" value="{{ old('penerbit', $buku->penerbit) }}" required></div>
                <div class="field"><label for="tahun_terbit">Tahun terbit</label><input id="tahun_terbit" type="number" name="tahun_terbit" value="{{ old('tahun_terbit', $buku->tahun_terbit) }}" min="1000" max="{{ date('Y') }}" required></div>
                <div class="field"><label for="stok">Jumlah stok</label><input id="stok" type="number" name="stok" value="{{ old('stok', $buku->stok) }}" min="0" required></div>
            </div>
            <div class="form-actions"><button class="button" type="submit">Simpan perubahan</button><a class="button button-quiet" href="{{ route('buku.index') }}">Batal</a></div>
        </form>
    </section>
@endsection