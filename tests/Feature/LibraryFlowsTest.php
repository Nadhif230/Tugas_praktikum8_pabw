<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LibraryFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_always_creates_a_member_account(): void
    {
        $this->post('/register', [
            'name' => 'Anggota Baru',
            'email' => 'anggota@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertRedirect('/login');

        $this->assertDatabaseHas('users', [
            'email' => 'anggota@example.test',
            'role' => 'user',
        ]);
    }

    public function test_members_cannot_open_user_management(): void
    {
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($member)
            ->get('/users')
            ->assertForbidden();
    }

    public function test_feature_pages_are_rendered_by_their_hmvc_modules(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/buku')
            ->assertOk()
            ->assertViewIs('buku::index');

        $this->get('/peminjaman')
            ->assertOk()
            ->assertViewIs('peminjaman::index');

        $this->get('/users')
            ->assertOk()
            ->assertViewIs('usermanagement::index');
    }

    public function test_admin_can_manage_user_roles_but_not_change_their_own_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/users', [
                'name' => 'Anggota Uji',
                'email' => 'anggota-uji@example.test',
                'password' => 'password123',
                'role' => 'user',
            ])
            ->assertRedirect(route('users.index'));

        $member = User::where('email', 'anggota-uji@example.test')->firstOrFail();
        $this->assertNotSame('password123', $member->password);

        $this->put("/users/{$member->id}", [
            'name' => $member->name,
            'email' => $member->email,
            'role' => 'admin',
        ])->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $member->id, 'role' => 'admin']);

        $this->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'user',
        ])->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_deleting_a_member_removes_evidence_and_restores_active_loan_stock(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);
        $book = Buku::create([
            'judul' => 'Buku Anggota',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'stok' => 0,
        ]);
        $path = 'bukti_peminjaman/bukti-anggota.pdf';
        Storage::disk('public')->put($path, 'proof');
        $borrow = Peminjaman::create([
            'user_id' => $member->id,
            'buku_id' => $book->id,
            'tanggal_pinjam' => '2026-11-17',
            'status' => 'Dipinjam',
            'bukti_peminjaman' => $path,
        ]);

        $this->actingAs($admin)
            ->delete("/users/{$member->id}")
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
        $this->assertDatabaseMissing('peminjamans', ['id' => $borrow->id]);
        $this->assertDatabaseHas('bukus', ['id' => $book->id, 'stok' => 1]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_creating_a_borrow_decrements_stock_and_rejects_unavailable_books(): void
    {
        $member = User::factory()->create(['role' => 'user']);
        $book = Buku::create([
            'judul' => 'Buku Uji',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'stok' => 1,
        ]);
        $payload = [
            'buku_id' => $book->id,
            'tanggal_pinjam' => '2026-11-17',
            'status' => 'Dipinjam',
        ];

        $this->actingAs($member)
            ->postJson('/peminjaman', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('bukus', ['id' => $book->id, 'stok' => 0]);

        $this->postJson('/peminjaman', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('buku_id');

        $this->assertDatabaseCount('peminjamans', 1);
        $this->assertDatabaseHas('bukus', ['id' => $book->id, 'stok' => 0]);
    }

    public function test_failed_borrow_update_does_not_restore_the_previous_book_stock(): void
    {
        $member = User::factory()->create(['role' => 'user']);
        $firstBook = Buku::create([
            'judul' => 'Buku Pertama',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'stok' => 0,
        ]);
        $unavailableBook = Buku::create([
            'judul' => 'Buku Kosong',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'stok' => 0,
        ]);
        $borrow = Peminjaman::create([
            'user_id' => $member->id,
            'buku_id' => $firstBook->id,
            'tanggal_pinjam' => '2026-11-17',
            'status' => 'Dipinjam',
        ]);

        $this->actingAs($member)
            ->putJson("/peminjaman/{$borrow->id}", [
                'buku_id' => $unavailableBook->id,
                'tanggal_pinjam' => '2026-11-17',
                'status' => 'Dipinjam',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('buku_id');

        $this->assertDatabaseHas('bukus', ['id' => $firstBook->id, 'stok' => 0]);
        $this->assertDatabaseHas('peminjamans', [
            'id' => $borrow->id,
            'buku_id' => $firstBook->id,
            'status' => 'Dipinjam',
        ]);
    }

    public function test_borrow_evidence_is_replaced_and_deleted_with_its_record(): void
    {
        Storage::fake('public');
        $member = User::factory()->create(['role' => 'user']);
        $book = Buku::create([
            'judul' => 'Buku Bukti',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'stok' => 1,
        ]);
        $payload = [
            'buku_id' => $book->id,
            'tanggal_pinjam' => '2026-11-17',
            'status' => 'Dipinjam',
        ];

        $this->actingAs($member)
            ->post('/peminjaman', [
                ...$payload,
                'bukti_peminjaman' => UploadedFile::fake()->create('bukti-awal.pdf', 32, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        $borrow = Peminjaman::firstOrFail();
        $oldPath = $borrow->bukti_peminjaman;
        Storage::disk('public')->assertExists($oldPath);

        $this->post("/peminjaman/{$borrow->id}", [
            '_method' => 'PUT',
            ...$payload,
            'bukti_peminjaman' => UploadedFile::fake()->create('bukti-baru.pdf', 32, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertOk();

        $newPath = $borrow->fresh()->bukti_peminjaman;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);

        $this->deleteJson("/peminjaman/{$borrow->id}")->assertOk();

        Storage::disk('public')->assertMissing($newPath);
        $this->assertDatabaseHas('bukus', ['id' => $book->id, 'stok' => 1]);
    }
}
