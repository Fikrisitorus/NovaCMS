<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * Klien HTTP untuk konsumsi API publik NovaCMS (apps/api).
 *
 * Semua panggilan frontend ke backend harus melewati class ini agar
 * base URL, timeout, dan penanganan error terpusat di satu tempat.
 */
class NovaApiClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeout = 15,
    ) {}

    /**
     * Ambil daftar post terbaru (endpoint /api/v1/posts).
     * Mengembalikan koleksi item pada key "data".
     */
    public function getRecentPosts(int $limit = 9): Collection
    {
        return $this->getCollection('/posts', ['per_page' => $limit]);
    }

    /**
     * Ambil satu post berdasarkan slug.
     */
    public function getPost(string $slug): ?array
    {
        return $this->getItem("/posts/{$slug}");
    }

    /**
     * Ambil post dalam sebuah kategori (mendukung pagination).
     */
    public function getCategoryPosts(string $slug, int $page = 1): array
    {
        return $this->getPaginated("/categories/{$slug}/posts", ['page' => $page]);
    }

    /**
     * Ambil daftar kategori lengkap dengan jumlah post-nya.
     */
    public function getCategories(): Collection
    {
        return $this->getCollection('/categories');
    }

    /**
     * Ambil daftar halaman yang sudah dipublikasi.
     */
    public function getPages(): Collection
    {
        return $this->getCollection('/pages');
    }

    /**
     * Ambil detail halaman berdasarkan slug.
     */
    public function getPage(string $slug): ?array
    {
        return $this->getItem("/pages/{$slug}");
    }

    /**
     * Ambil halaman homepage berdasarkan slug "home".
     * Mungkin null bila belum dibuat oleh editor.
     */
    public function getHomePage(): ?array
    {
        return $this->getPage('home');
    }

    /**
     * Ambil koleksi item dari endpoint yang mengembalikan { data: [...] }.
     * Mengembalikan koleksi kosong bila API tidak terjangkau.
     */
    public function getCollection(string $path, array $query = []): Collection
    {
        $response = $this->request($path, $query);

        return collect($response['data'] ?? []);
    }

    /**
     * Ambil satu item dari endpoint resource tunggal.
     *
     * API publik membungkus response tunggal di key "data"
     * (lihat PageResource/PostResource), jadi unwrap ke isinya.
     * Mengembalikan null bila API tidak terjangkau atau 404.
     */
    public function getItem(string $path, array $query = []): ?array
    {
        $response = $this->request($path, $query);

        return $response['data'] ?? null;
    }

    /**
     * Ambil payload pagination { data, meta } untuk list bertabel.
     */
    public function getPaginated(string $path, array $query = []): array
    {
        $response = $this->request($path, $query);

        return [
            'items' => collect($response['data'] ?? []),
            'meta' => $response['meta'] ?? $this->emptyMeta(),
        ];
    }

    /**
     * Jalankan satu request GET ke API dan decode JSON-nya.
     *
     * Setiap kegagalan jaringan dilempar ke handler global lewat report(),
     * lalu dilepas sebagai array kosong agar halaman tetap dirender tanpa
     * menghempaskan 500 ke pengunjung.
     */
    private function request(string $path, array $query = []): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl.$path, $query);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            // 404 berarti konten belum ada — bukan error yang perlu dilog.
            if ($response->status() === 404) {
                return [];
            }

            report("NovaCMS API mengembalikan status {$response->status()} untuk {$path}");
        } catch (\Throwable $e) {
            report($e);
        }

        return [];
    }

    /**
     * Default meta pagination ketika API tidak memberikan balasan.
     */
    private function emptyMeta(): array
    {
        return [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => 0,
        ];
    }
}
