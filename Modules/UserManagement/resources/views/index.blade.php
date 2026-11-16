@extends('layouts.app')

@section('title', 'Manajemen Pengguna | SiPustaka')

@section('content')
    <div class="page-heading">
        <div><div class="eyebrow">Administrasi</div><h1>Pengelolaan pengguna</h1><p class="subtitle">Buat akun, perbarui data dan atur peran pengguna.</p></div>
        <span class="badge">{{ $users->total() }} akun</span>
    </div>

    @if (session('success'))<div class="alert">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <section class="card">
        <div class="card-heading"><div><h2>Tambah pengguna</h2><p class="subtitle">Akun yang dibuat admin dapat memiliki peran admin atau anggota.</p></div></div>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="field"><label for="new-name">Nama lengkap</label><input id="new-name" name="name" value="{{ old('name') }}" required></div>
                <div class="field"><label for="new-email">Email</label><input id="new-email" type="email" name="email" value="{{ old('email') }}" required></div>
                <div class="field"><label for="new-password">Kata sandi</label><input id="new-password" type="password" name="password" minlength="8" required></div>
                <div class="field"><label for="new-role">Peran</label><select id="new-role" name="role" required><option value="user">Anggota</option><option value="admin" @selected(old('role') === 'admin')>Admin</option></select></div>
            </div>
            <button class="button" type="submit">Tambahkan pengguna</button>
        </form>
    </section>

    <section class="card">
        <div class="card-heading"><div><h2>Daftar akun</h2><p class="subtitle">Perbarui informasi atau hapus akun yang tidak digunakan.</p></div></div>
        @forelse ($users as $user)
            <article class="user-card">
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="form-grid">
                        <div class="field"><label for="name-{{ $user->id }}">Nama</label><input id="name-{{ $user->id }}" name="name" value="{{ $user->name }}" required></div>
                        <div class="field"><label for="email-{{ $user->id }}">Email</label><input id="email-{{ $user->id }}" type="email" name="email" value="{{ $user->email }}" required></div>
                        <div class="field"><label for="password-{{ $user->id }}">Kata sandi baru <span class="muted">(opsional)</span></label><input id="password-{{ $user->id }}" type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Kosongkan jika tidak diubah"><span class="field-hint">Isi konfirmasi jika ingin mengganti kata sandi.</span></div>
                        <div class="field"><label for="password-confirmation-{{ $user->id }}">Konfirmasi kata sandi</label><input id="password-confirmation-{{ $user->id }}" type="password" name="password_confirmation" minlength="8" autocomplete="new-password"></div>
                        <div class="field">
                            <label for="role-{{ $user->id }}">Peran</label>
                            @if ($user->is(auth()->user()))
                                <span class="badge">Admin (akun Anda)</span>
                            @else
                                <select id="role-{{ $user->id }}" name="role" required>
                                    <option value="user" @selected($user->role === 'user')>Anggota</option>
                                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                </select>
                            @endif
                        </div>
                    </div>
                    <div class="form-actions">
                        <button class="button button-secondary" type="submit">Simpan perubahan</button>
                        @unless ($user->is(auth()->user()))
                            <button class="button button-danger" type="submit" form="delete-user-{{ $user->id }}">Hapus akun</button>
                        @endunless
                    </div>
                </form>
                @unless ($user->is(auth()->user()))
                    <form id="delete-user-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun {{ $user->name }}?')">
                        @csrf @method('DELETE')
                    </form>
                @endunless
            </article>
        @empty
            <div class="empty-state"><strong>Belum ada pengguna</strong>Akun yang terdaftar akan tampil di sini.</div>
        @endforelse
        <div class="pagination-wrap">{{ $users->links() }}</div>
    </section>
@endsection