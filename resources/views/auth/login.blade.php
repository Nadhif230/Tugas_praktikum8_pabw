@extends('layouts.app')

@section('title', 'Masuk | SiPustaka')

@section('content')
    <section class="auth-card">
        <div class="auth-brand">
            <span class="brand-mark">S</span>
            <h1>Selamat datang</h1>
            <p>Masuk untuk melanjutkan ke SiPustaka.</p>
        </div>

        @if (session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required autofocus>
            </div>
            <div class="field">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
            </div>
            <button class="button" type="submit">Masuk ke akun</button>
        </form>
        <p class="auth-footer">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai anggota</a></p>
    </section>
@endsection