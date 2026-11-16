<?php

namespace Modules\Peminjaman\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePeminjamanRequest;
use App\Http\Requests\UpdatePeminjamanRequest;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class PeminjamanController extends Controller
{
    public function index()
    {
        $bukus = Buku::query()->orderBy('judul')->get();

        return view('peminjaman::index', compact('bukus'));
    }

    public function data()
    {
        $query = Peminjaman::with(['user:id,name', 'buku:id,judul']);

        if (Auth::user()->role === 'user') {
            $query->where('user_id', Auth::id());
        }

        $peminjamans = $query->latest()->get();

        $peminjamans->each(function (Peminjaman $peminjaman) {
            $peminjaman->bukti_url = $peminjaman->bukti_peminjaman
                ? Storage::disk('public')->url($peminjaman->bukti_peminjaman)
                : null;
        });

        return response()->json($peminjamans);
    }

    public function store(StorePeminjamanRequest $request)
    {
        $validated = $request->validated();
        $path = null;

        try {
            if ($request->hasFile('bukti_peminjaman')) {
                $path = $request->file('bukti_peminjaman')
                    ->store('bukti_peminjaman', 'public');

                if ($path === false) {
                    throw new \RuntimeException('Bukti peminjaman gagal disimpan.');
                }

                $validated['bukti_peminjaman'] = $path;
            }

            $peminjaman = DB::transaction(function () use ($validated) {
                $buku = Buku::query()
                    ->lockForUpdate()
                    ->findOrFail($validated['buku_id']);

                if ($validated['status'] === 'Dipinjam') {
                    if ($buku->stok < 1) {
                        throw ValidationException::withMessages([
                            'buku_id' => 'Stok buku tidak tersedia.',
                        ]);
                    }

                    $buku->decrement('stok');
                }

                return Peminjaman::create([
                    ...$validated,
                    'user_id' => Auth::id(),
                ]);
            });
        } catch (Throwable $exception) {
            if (is_string($path)) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Peminjaman berhasil ditambahkan.',
            'data' => $peminjaman->load(['user:id,name', 'buku:id,judul']),
        ], 201);
    }

    public function update(UpdatePeminjamanRequest $request, Peminjaman $peminjaman)
    {
        $validated = $request->validated();

        abort_if(
            Auth::user()->role === 'user' && $peminjaman->user_id !== Auth::id(),
            403
        );

        $previousPath = $peminjaman->bukti_peminjaman;
        $newPath = null;

        try {
            if ($request->hasFile('bukti_peminjaman')) {
                $newPath = $request->file('bukti_peminjaman')
                    ->store('bukti_peminjaman', 'public');

                if ($newPath === false) {
                    throw new \RuntimeException('Bukti peminjaman gagal disimpan.');
                }

                $validated['bukti_peminjaman'] = $newPath;
            }

            DB::transaction(function () use ($peminjaman, $validated) {
                $current = Peminjaman::query()
                    ->lockForUpdate()
                    ->findOrFail($peminjaman->id);

                $bookIds = array_unique([
                    (int) $current->buku_id,
                    (int) $validated['buku_id'],
                ]);
                sort($bookIds);
                $books = Buku::query()
                    ->whereIn('id', $bookIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $oldBook = $books->get($current->buku_id);
                $newBook = $books->get($validated['buku_id']);
                $wasBorrowed = $current->status === 'Dipinjam';
                $willBeBorrowed = $validated['status'] === 'Dipinjam';
                $bookChanged = $current->buku_id !== (int) $validated['buku_id'];

                if ($willBeBorrowed && (! $wasBorrowed || $bookChanged) && $newBook->stok < 1) {
                    throw ValidationException::withMessages([
                        'buku_id' => 'Stok buku tidak tersedia.',
                    ]);
                }

                if ($wasBorrowed && (! $willBeBorrowed || $bookChanged)) {
                    $oldBook->increment('stok');
                }

                if ($willBeBorrowed && (! $wasBorrowed || $bookChanged)) {
                    $newBook->decrement('stok');
                }

                $current->update($validated);
            });
        } catch (Throwable $exception) {
            if (is_string($newPath)) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if (is_string($newPath) && $previousPath !== null) {
            if (! Storage::disk('public')->delete($previousPath)) {
                throw new \RuntimeException('Bukti peminjaman lama gagal dihapus dari penyimpanan.');
            }
        }

        return response()->json([
            'message' => 'Peminjaman berhasil diperbarui.',
            'data' => $peminjaman->fresh()->load(['user:id,name', 'buku:id,judul']),
        ]);
    }

    public function destroy(Peminjaman $peminjaman)
    {
        abort_if(
            Auth::user()->role === 'user' && $peminjaman->user_id !== Auth::id(),
            403
        );

        $path = $peminjaman->bukti_peminjaman;

        DB::transaction(function () use ($peminjaman) {
            $current = Peminjaman::query()
                ->lockForUpdate()
                ->findOrFail($peminjaman->id);

            if ($current->status === 'Dipinjam') {
                Buku::query()
                    ->whereKey($current->buku_id)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->increment('stok');
            }

            $current->delete();
        });

        if ($path !== null) {
            if (! Storage::disk('public')->delete($path)) {
                throw new \RuntimeException('Bukti peminjaman gagal dihapus dari penyimpanan.');
            }
        }

        return response()->json([
            'message' => 'Peminjaman berhasil dihapus.',
        ]);
    }
}
