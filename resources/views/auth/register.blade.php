@extends('layouts.app')

@section('title', 'Daftar | SiPustaka')

@section('content')
    <section class="auth-card">
        <div class="auth-brand">
            <span class="brand-mark">S</span>
            <h1>Buat akun anggota</h1>
            <p>Daftar untuk mulai menggunakan layanan perpustakaan.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="field">
                <label for="name">Nama lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Nama lengkap" autocomplete="name" required>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" name="password" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
            </div>
            <div class="field">
                <label for="password_confirmation">Ulangi kata sandi</label>
                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi kata sandi" autocomplete="new-password" required>
            </div>
            <button class="button" type="submit">Buat akun</button>
        </form>
        <p class="auth-footer">Sudah terdaftar? <a href="{{ route('login') }}">Masuk di sini</a></p>
    </section>
@endsection