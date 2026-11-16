<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SiPustaka')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ auth()->check() ? 'app-shell' : 'auth-shell' }}">
    @auth
        <header class="topbar">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">S</span>
                <span>SiPustaka<small>Perpustakaan digital</small></span>
            </a>
            <nav class="main-nav" aria-label="Navigasi utama">
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Beranda</a>
                <a class="{{ request()->routeIs('buku.*') ? 'active' : '' }}" href="{{ route('buku.index') }}">Koleksi Buku</a>
                <a class="{{ request()->routeIs('peminjaman.*') ? 'active' : '' }}" href="{{ route('peminjaman.index') }}">Peminjaman</a>
                @if (auth()->user()->role === 'admin')
                    <a class="{{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Pengguna</a>
                @endif
            </nav>
            <div class="account">
                <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="account-name">{{ auth()->user()->name }}<small>{{ ucfirst(auth()->user()->role) }}</small></span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="button button-quiet button-small" type="submit">Keluar</button>
                </form>
            </div>
        </header>
    @endauth

    <main class="{{ auth()->check() ? 'page-wrap' : 'auth-wrap' }}">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
