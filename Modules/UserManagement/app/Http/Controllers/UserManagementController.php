<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::query()->latest()->paginate(10);

        return view('usermanagement::index', compact('users'));
    }

    public function store(StoreUserRequest $request)
    {
        User::create($request->validated());

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if ($user->is(Auth::user())) {
            unset($validated['role']);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->is(Auth::user())) {
            return back()->withErrors([
                'user' => 'Anda tidak dapat menghapus akun sendiri.',
            ]);
        }

        $files = DB::transaction(function () use ($user) {
            $currentUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $peminjamans = Peminjaman::query()
                ->where('user_id', $currentUser->id)
                ->lockForUpdate()
                ->get(['id', 'buku_id', 'status', 'bukti_peminjaman']);

            $activeLoans = $peminjamans->where('status', 'Dipinjam');
            $books = Buku::query()
                ->whereIn('id', $activeLoans->pluck('buku_id')->unique())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($activeLoans->groupBy('buku_id') as $bookId => $loans) {
                $book = $books->get($bookId);
                if ($book === null) {
                    throw new \RuntimeException('Buku pada riwayat peminjaman tidak ditemukan.');
                }

                $book->increment('stok', $loans->count());
            }

            $files = $peminjamans->pluck('bukti_peminjaman')
                ->filter()
                ->all();

            $currentUser->delete();

            return $files;
        });

        if ($files !== [] && ! Storage::disk('public')->delete($files)) {
            throw new \RuntimeException('Sebagian bukti peminjaman gagal dihapus dari penyimpanan.');
        }

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
