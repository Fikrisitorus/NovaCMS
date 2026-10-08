<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Revision;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test fitur version history untuk Page & Post.
 *
 * Revision disebut juga "snapshot": salinan konten SEBELUM update,
 * disimpan otomatis oleh trait HasRevisions ke tabel content_revisions.
 *
 * 1. Update konten Page/Post harus membuat record revision baru.
 * 2. Revision mencatat user_id dari user yang sedang login.
 * 3. Restore memulihkan konten lama dan membuat revision baru dari
 *    kondisi konten sebelum di-restore.
 *
 * Catatan: konten Page disimpan di kolom JSON `blocks`, sedangkan Post
 * di kolom `content` (HTML string) — keduanya diuji.
 */
class RevisionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->website = Website::create([
            'name' => 'Site Test',
            'domain' => 'test.example.com',
        ]);

        $this->user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
    }

    public function test_update_page_membuat_revision_baru(): void
    {
        $page = Page::create([
            'website_id' => $this->website->id,
            'title' => 'Beranda',
            'slug' => 'beranda',
            'is_published' => true,
            'blocks' => [
                ['type' => 'hero', 'data' => ['heading' => 'Judul Lama']],
            ],
        ]);

        $this->assertDatabaseCount('content_revisions', 0);

        $page->update([
            'blocks' => [
                ['type' => 'hero', 'data' => ['heading' => 'Judul Baru']],
            ],
        ]);

        $this->assertDatabaseCount('content_revisions', 1);

        $revision = Revision::first();

        // Snapshot merekam kondisi SEBELUM update.
        $this->assertSame('App\Models\Page', $revision->revisable_type);
        $this->assertSame($page->id, $revision->revisable_id);
        $this->assertSame('blocks', $revision->content['attribute']);
        $this->assertSame(
            [['type' => 'hero', 'data' => ['heading' => 'Judul Lama']]],
            $revision->content['data']
        );

        // Relasi polymorphic dari induk ke revisions.
        $this->assertCount(1, $page->refresh()->revisions);
        $this->assertSame($revision->id, $page->latestRevision()->id);
    }

    public function test_update_post_membuat_revision_baru(): void
    {
        $post = Post::create([
            'website_id' => $this->website->id,
            'author_id' => $this->user->id,
            'title' => 'Artikel',
            'slug' => 'artikel',
            'content' => '<p>Konten lama</p>',
        ]);

        $post->update(['content' => '<p>Konten baru</p>']);

        $this->assertDatabaseCount('content_revisions', 1);

        $revision = Revision::first();

        $this->assertSame('App\Models\Post', $revision->revisable_type);
        $this->assertSame('content', $revision->content['attribute']);
        $this->assertSame('<p>Konten lama</p>', $revision->content['data']);
    }

    public function test_revision_mencatat_user_id_yang_login(): void
    {
        $this->actingAs($this->user);

        $page = Page::create([
            'website_id' => $this->website->id,
            'title' => 'Tentang',
            'slug' => 'tentang',
            'blocks' => ['type' => 'hero', 'data' => ['heading' => 'A']],
        ]);

        $page->update(['blocks' => ['type' => 'hero', 'data' => ['heading' => 'B']]]);

        $this->assertDatabaseCount('content_revisions', 1);

        $revision = Revision::first();

        $this->assertSame($this->user->id, $revision->user_id);
        $this->assertSame($this->user->name, $revision->user->name);
    }

    public function test_revision_tanpa_user_login_memiliki_user_id_null(): void
    {
        $post = Post::create([
            'website_id' => $this->website->id,
            'author_id' => $this->user->id,
            'title' => 'Tanpa User',
            'slug' => 'tanpa-user',
            'content' => '<p>A</p>',
        ]);

        $post->update(['content' => '<p>B</p>']);

        $this->assertNull(Revision::first()->user_id);
    }

    public function test_revision_pesan_commit_tersimpan(): void
    {
        $post = Post::create([
            'website_id' => $this->website->id,
            'author_id' => $this->user->id,
            'title' => 'Pesan',
            'slug' => 'pesan',
            'content' => '<p>A</p>',
        ]);

        $post->revision_summary = 'Ganti paragraf pertama';
        $post->update(['content' => '<p>B</p>']);

        $this->assertSame('Ganti paragraf pertama', Revision::first()->summary);
    }

    public function test_restore_page_mengembalikan_konten_versi_lama(): void
    {
        $page = Page::create([
            'website_id' => $this->website->id,
            'title' => 'Kontak',
            'slug' => 'kontak',
            'blocks' => [
                ['type' => 'contact', 'data' => ['recipient_email' => 'lama@example.com']],
            ],
        ]);

        $page->update([
            'blocks' => [
                ['type' => 'contact', 'data' => ['recipient_email' => 'baru@example.com']],
            ],
        ]);

        $revision = Revision::first();

        // Restore: konten saat ini (baru@example.com) dipulihkan ke snapshot.
        $result = $revision->restore();

        $this->assertTrue($result);

        $page->refresh();

        $this->assertSame(
            [
                ['type' => 'contact', 'data' => ['recipient_email' => 'lama@example.com']],
            ],
            $page->blocks
        );

        // Restore menyimpan kondisi sebelumnya sebagai revision baru,
        // sehingga riwayat tetap utuh dan bisa di-rollback lagi.
        $this->assertDatabaseCount('content_revisions', 2);

        $latest = $page->refresh()->latestRevision();

        $this->assertSame(
            [
                ['type' => 'contact', 'data' => ['recipient_email' => 'baru@example.com']],
            ],
            $latest->content['data']
        );
    }

    public function test_restore_post_mengembalikan_konten_versi_lama(): void
    {
        $post = Post::create([
            'website_id' => $this->website->id,
            'author_id' => $this->user->id,
            'title' => 'Rollback',
            'slug' => 'rollback',
            'content' => '<p>Versi 1</p>',
        ]);

        // Tiap update di-snapshot terpisah agar revision pertama selalu
        // merekam "Versi 1" (snapshot diambil saat 'updating' event).
        $post->update(['content' => '<p>Versi 2</p>']);
        $snapshotPertama = $post->latestRevision();
        $this->assertSame('<p>Versi 1</p>', $snapshotPertama->content['data']);

        $post->update(['content' => '<p>Versi 3</p>']);
        $this->assertSame('<p>Versi 2</p>', $post->latestRevision()->content['data']);

        // Restore snapshot pertama memulihkan "Versi 1".
        $snapshotPertama->restore();

        $this->assertSame('<p>Versi 1</p>', $post->refresh()->content);

        // Versi 3 (kondisi sebelum restore) ikut tersimpan ke history.
        $this->assertDatabaseCount('content_revisions', 3);
        $this->assertSame(
            '<p>Versi 3</p>',
            $post->latestRevision()->content['data']
        );

        // Riwayat bisa di-rollback berulang kali.
        $snapshotPertama->restore();
        $this->assertSame('<p>Versi 1</p>', $post->refresh()->content);
    }

    public function test_restore_dengan_pesan_commit(): void
    {
        $post = Post::create([
            'website_id' => $this->website->id,
            'author_id' => $this->user->id,
            'title' => 'Commit',
            'slug' => 'commit',
            'content' => '<p>A</p>',
        ]);

        $post->update(['content' => '<p>B</p>']);

        $revision = Revision::first();

        $revision->restore('Rollback ke konten awal');

        $revisionBaru = $post->latestRevision();

        $this->assertSame('Rollback ke konten awal', $revisionBaru->summary);
        $this->assertSame('<p>B</p>', $revisionBaru->content['data']);
    }

    public function test_restore_gagal_ketika_induk_sudah_dihapus(): void
    {
        $post = Post::create([
            'website_id' => $this->website->id,
            'author_id' => $this->user->id,
            'title' => 'Hapus',
            'slug' => 'hapus',
            'content' => '<p>A</p>',
        ]);

        $post->update(['content' => '<p>B</p>']);

        $revision = Revision::first();

        // Revision mestinya ikut terhapus saat induk di-delete (FK cascade
        // tidak dipasang, jadi record orphan tetap ada — restore harus aman).
        $post->delete();

        $this->assertFalse($revision->restore());
        $this->assertDatabaseCount('content_revisions', 1);
    }

    public function test_update_tanpa_perubahan_konten_tidak_membuat_revision(): void
    {
        $page = Page::create([
            'website_id' => $this->website->id,
            'title' => 'Statis',
            'slug' => 'statis',
            'is_published' => false,
            'blocks' => ['type' => 'hero', 'data' => ['heading' => 'A']],
        ]);

        // Mengubah atribut selain konten tidak boleh membuat snapshot.
        $page->update(['is_published' => true]);

        $this->assertDatabaseCount('content_revisions', 0);
    }

    public function test_formatted_content_menampilkan_json_pretty_print(): void
    {
        $post = Post::create([
            'website_id' => $this->website->id,
            'author_id' => $this->user->id,
            'title' => 'Format',
            'slug' => 'format',
            'content' => '<p>A</p>',
        ]);

        $post->update(['content' => '<p>B</p>']);

        $formatted = Revision::first()->formattedContent();

        $this->assertStringContainsString('"attribute": "content"', $formatted);
        $this->assertStringContainsString("\n", $formatted);

        $decoded = json_decode($formatted, true);

        $this->assertSame('content', $decoded['attribute']);
        $this->assertSame('<p>A</p>', $decoded['data']);
    }
}
