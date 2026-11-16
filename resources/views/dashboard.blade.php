@extends('layouts.app')

@section('title', 'Beranda | SiPustaka')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">Ruang baca digital</div>
            <h1>Selamat datang, {{ auth()->user()->name }}.</h1>
            <p class="subtitle">Kelola koleksi dan aktivitas perpustakaan dengan mudah.</p>
        </div>
        <span class="badge">{{ auth()->user()->role === 'admin' ? 'Administrator' : 'Anggota' }}</span>
    </div>

    <section class="stats-grid" aria-label="Ringkasan perpustakaan">
        <article class="stat-card"><span>Total koleksi buku</span><strong>{{ $jumlahBuku }}</strong></article>
        <article class="stat-card"><span>Peminjaman aktif</span><strong>{{ $peminjamanAktif }}</strong></article>
        <article class="stat-card"><span>Anggota terdaftar</span><strong>{{ $jumlahAnggota }}</strong></article>
    </section>

    <section class="card">
        <div class="card-heading">
            <div><h2>Akses cepat</h2><p class="subtitle">Pilih layanan yang ingin dibuka.</p></div>
        </div>
        <div class="quick-links">
            <a class="quick-link" href="{{ route('buku.index') }}"><strong>Jelajahi koleksi buku</strong><span>Temukan judul, penulis, atau penerbit favorit.</span></a>
            <a class="quick-link" href="{{ route('peminjaman.index') }}"><strong>Kelola peminjaman</strong><span>Lihat dan perbarui catatan peminjaman buku.</span></a>
            @if (auth()->user()->role === 'admin')
                <a class="quick-link" href="{{ route('users.index') }}"><strong>Kelola pengguna</strong><span>Atur akun anggota dan hak akses sistem.</span></a>
            @else
                <a class="quick-link" href="{{ route('buku.index') }}"><strong>Mulai membaca</strong><span>Lihat buku yang tersedia di perpustakaan.</span></a>
            @endif
        </div>
    </section>
@endsection