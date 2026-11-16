@extends('layouts.app')

@section('title', 'Peminjaman Digital | SiPustaka')

@section('content')
    <div class="page-heading">
        <div><div class="eyebrow">Layanan perpustakaan</div><h1>Peminjaman buku</h1><p class="subtitle">Catat peminjaman, pembaruan status, dan bukti pengembalian.</p></div>
    </div>

    <section class="card peminjaman-form">
        <div class="card-heading"><div><h2 id="formTitle">Catat peminjaman</h2><p class="subtitle">Unggah bukti opsional dengan ukuran maksimal 2 MB.</p></div></div>
        <div id="pesan" class="alert" role="status" hidden></div>
        <form id="formPeminjaman" data-show-borrower="{{ auth()->user()->role === 'admin' ? 'true' : 'false' }}" data-list-url="{{ route('peminjaman.data') }}" data-store-url="{{ route('peminjaman.store') }}" data-update-url="{{ route('peminjaman.update', ['peminjaman' => '__ID__']) }}" data-delete-url="{{ route('peminjaman.destroy', ['peminjaman' => '__ID__']) }}">
            @csrf
            <input type="hidden" id="peminjaman_id">
            <div class="form-grid">
                <div class="field"><label for="buku_id">Buku</label><select name="buku_id" id="buku_id" required><option value="">Pilih buku</option>@foreach ($bukus as $buku)<option value="{{ $buku->id }}">{{ $buku->judul }} — stok {{ $buku->stok }}</option>@endforeach</select></div>
                <div class="field"><label for="tanggal_pinjam">Tanggal pinjam</label><input type="date" name="tanggal_pinjam" id="tanggal_pinjam" required></div>
                <div class="field"><label for="tanggal_kembali">Tanggal kembali</label><input type="date" name="tanggal_kembali" id="tanggal_kembali"></div>
                <div class="field"><label for="status">Status</label><select name="status" id="status" required><option value="Dipinjam">Dipinjam</option><option value="Dikembalikan">Dikembalikan</option></select></div>
                <div class="field"><label for="bukti_peminjaman">Bukti peminjaman</label><input type="file" name="bukti_peminjaman" id="bukti_peminjaman" accept=".jpg,.jpeg,.png,.pdf"><span class="field-hint">Format JPG, PNG, atau PDF; maksimal 2 MB.</span><span id="currentFile" class="current-file" hidden></span></div>
            </div>
            <div class="form-actions"><button class="button" id="submitButton" type="submit">Simpan peminjaman</button><button class="button button-quiet" id="cancelEdit" type="button" hidden>Batal edit</button></div>
        </form>
    </section>

    <section class="card">
        <div class="card-heading"><div><h2>Riwayat peminjaman</h2><p class="subtitle">Data diperbarui langsung tanpa memuat ulang halaman.</p></div></div>
        <div class="table-wrap">
            <table>
                <thead><tr>@if (auth()->user()->role === 'admin')<th>Peminjam</th>@endif<th>Buku</th><th>Tanggal pinjam</th><th>Tanggal kembali</th><th>Status</th><th>Bukti</th><th>Aksi</th></tr></thead>
                <tbody id="dataPeminjaman"><tr><td colspan="{{ auth()->user()->role === 'admin' ? 7 : 6 }}"><div class="empty-state">Memuat data peminjaman...</div></td></tr></tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/peminjaman.js') }}" defer></script>
@endpush