<h2>Manajemen Pengguna</h2>

@if (session('success'))
    <p>{{ session('success') }}</p>
@endif

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <p style="color:red">{{ $error }}</p>
    @endforeach
@endif

<h3>Tambah Pengguna</h3>

<form action="{{ route('users.store') }}" method="POST">
    @csrf

    <input type="text" name="name"
        placeholder="Nama" required>

    <input type="email" name="email"
        placeholder="Email" required>

    <input type="password" name="password"
        placeholder="Password" required>

    <select name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select>

    <button type="submit">Tambah</button>
</form>

<h3>Daftar Pengguna</h3>

<table border="1" cellpadding="10">
    <tr>
        <th>Nama</th>
        <th>Email</th>
        <th>Role</th>
        <th>Aksi</th>
    </tr>

    @foreach ($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role }}</td>
            <td>
                <form action="{{ route('users.update', $user) }}"
                    method="POST">
                    @csrf
                    @method('PUT')

                    <input type="text" name="name"
                        value="{{ $user->name }}" required>

                    <input type="email" name="email"
                        value="{{ $user->email }}" required>

                    <select name="role">
                        <option value="user"
                            @selected($user->role === 'user')>
                            User
                        </option>

                        <option value="admin"
                            @selected($user->role === 'admin')>
                            Admin
                        </option>
                    </select>

                    <button type="submit">Update</button>
                </form>

                <form action="{{ route('users.destroy', $user) }}"
                    method="POST">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        onclick="return confirm('Hapus pengguna?')">
                        Hapus
                    </button>
                </form>
            </td>
        </tr>
    @endforeach
</table>

{{ $users->links() }}