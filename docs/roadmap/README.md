# Roadmap NovaCMS

**Tanggal Update Terakhir:** 6 Oktober 2026

> **Wajib dibaca oleh:** Project Manager, Developer, dan *Stakeholder*.
> Status setiap item diambil dari **kode yang ada saat ini**, bukan dari rencana tertulis.

---

## 📖 Pengantar

Roadmap diambil dari [README](../../README.md#-roadmap) proyek dan di-cross-check dengan kondisi *codebase* per 6 Oktober 2026. Setiap item diberi penanda status:

| Penanda | Arti                                                                 |
| ------- | -------------------------------------------------------------------- |
| ✅ **Sudah ada** | Fitur sudah ada di kode dan bisa dipakai.                              |
| 🟡 **Sebagian**  | Fondasi/inti sudah ada, tapi ada bagian penting yang belum selesai.    |
| ⬜ **Belum ada** | Fitur belum dimulai atau hanya tertulis di README.                     |

---

## 📊 Ringkasan Eksekutif

| Fase   | Tema                        | Status      |
| ------ | --------------------------- | ----------- |
| Phase 1 | Auth, User, Role & Permission | ✅ Selesai   |
| Phase 2 | Website, Pages, Sections    | 🟡 Sebagian  |
| Phase 3 | Media, Blog, SEO            | 🟡 Sebagian  |
| Phase 4 | API, Search, Queue          | 🟡 Sebagian  |
| Phase 5 | Visual Builder, Plugin      | ⬜ Belum mulai |
| Phase 6 | Multi Tenant, Marketplace   | ⬜ Belum mulai |

**Fokus selanjutnya:** menyelesaikan *gap* Phase 2–4 (lihat [Prioritas Lanjutan](#-prioritas-lanjutan)).

---

## 🏗 Phase 1 — Authentikasi, User, Role & Permission

| Item                  | Status | Bukti di Kode                                              |
| --------------------- | ------ | ---------------------------------------------------------- |
| Authentication        | ✅     | `users` (UUID, password hashed), panel `/admin` + `->login()`, `sessions` & `password_reset_tokens`. |
| User Management       | ✅     | `Filament/Resources/UserResource` (List/Create/Edit), model `User` memakai `Authenticatable` + `HasFactory`. |
| Role & Permission     | ✅     | `spatie/laravel-permission` ^6.25 + `bezhansalleh/filament-shield` ^3.9 (`FilamentShieldPlugin` di `AdminPanelProvider`); 5 tabel permission termigrasi. |

**Catatan:**
- Login ke admin panel sudah berjalan. Tidak ada endpoint API autentikasi (login/refresh/token) — API bersifat publik *read-only*.
- Email verifikasi & *reset password flow* belum diaktifkan (`email_verified_at` tersedia tapi belum digunakan).

---

## 📄 Phase 2 — Website, Pages, Sections

| Item                   | Status | Bukti di Kode                                              |
| ---------------------- | ------ | ---------------------------------------------------------- |
| Website Management     | ✅     | Tabel `websites` + `Filament/Resources/WebsiteResource` (CRUD + relasi `PagesRelationManager`). |
| Multi Page Management  | ✅     | Tabel `pages` FK ke `websites`, filter `?website_id=` di API, daftar halaman muncul di `websites/{domain}`. |
| Dynamic Sections       | 🟡     | **Model berbasis blok sudah ada**, tapi belum "dinamis" sepenuhnya: konten halaman dipindah ke kolom JSON `blocks` (migrasi `2026_09_10_094631` **men-drop `page_sections`**). |
| Draft & Publish (Page) | 🟡     | Toggle `is_published` ada; tapi **tidak ada preview *draft***, **tidak ada versi/*revision***, dan **tidak ada penjadwalan** pada Page. |

**Yang belum:** preview *draft* sebelum publikasi, *version history* (lihat juga Phase 4), dan *workflow* approval.

---

## 📝 Phase 3 — Media, Blog, SEO

| Item                    | Status | Bukti di Kode                                              |
| ----------------------- | ------ | ---------------------------------------------------------- |
| Media Library           | ✅     | Tabel `media` (UUID, path, disk, alt_text, caption) + `Filament/Resources/MediaResource` (upload, detail). Storage S3-compatible (RustFS). |
| Blog System             | 🟡     | Tabel `posts` (title, slug, featured_image, excerpt, content, is_published, published_at) + `categories` / pivot `category_post` + `Filament Resources` `PostResource` & `CategoryResource` + blog Blade `routes/web.php`. **Tapi endpoint API publik belum mengekspos `featured_image`, `excerpt`, `categories`, dan `author`** (lihat [docs/api](../api/README.md#-gap--catatan-implementasi)). |
| SEO Management          | 🟡     | Trait `HasSeo` (morphOne `seo_metas`) + form SEO di `PageResource` & `PostResource` sudah ada. **Tapi metadata SEO belum dikembalikan oleh API publik** — `PostResource`/`PageResource` tidak memuat `seoMeta`. |
| Scheduled Post          | ✅     | Filter `published_at <= now()` di API publik & blog Blade, dilindungi *test* `ApiPagePostPublishTest`. |

**Yang belum:** endpoint kategori publik, ekspos SEO di API, *image transformation*/optimasi, dan pemetaan media yang dipakai.

---

## 🔌 Phase 4 — API, Search, Queue, Cache

| Item                    | Status | Bukti di Kode                                              |
| ----------------------- | ------ | ---------------------------------------------------------- |
| REST API (publik)       | ✅     | 6 *endpoint* `/api/v1/*` (websites, pages, posts) dengan resource transformer. Lihat [docs/api](../api/README.md). |
| API — internal/admin     | ⬜     | Belum ada endpoint tulis (create/update/delete) terpisah; manipulasi data hanya lewat Filament. |
| Search                  | ⬜     | Belum ada. Meilisearch/`scout` tidak ada di `composer.json` maupun `docker-compose.yml`; pencarian hanya `searchable()` di kolom tabel Filament. |
| Queue                   | 🟡     | Redis & `QUEUE_CONNECTION=redis` terkonfigurasi; tabel `jobs`/`job_batches`/`failed_jobs` ada. **Belum ada job/job dispatch apapun** yang ditulis. |
| Cache                   | 🟡     | Redis & `CACHE_STORE=redis` terkonfigurasi. **Endpoint publik belum memakai `Cache::remember`/ETag.** |
| Version History         | ⬜     | Tidak ada skema versi/*revision* pada Page/Post. |
| Activity Log            | ⬜     | Tidak ada. |

**Yang belum:** paginasi API, *rate limiting*, API key, OpenAPI/Swagger, koleksi Postman.

---

## 🎨 Phase 5 — Visual Builder & Plugin System

| Item                  | Status | Bukti di Kode                                              |
| ----------------------| ------ | ---------------------------------------------------------- |
| Visual Builder        | 🟡     | Fondasi sudah ada: **JSON *blocks builder*** di form Filament (hero, faq, gallery, pricing, contact). Yang belum ada adalah *drag-and-drop visual* di kanvas. |
| Custom Components     | ⬜     | Tipe blok masih *hardcode* 5 tipe di `PageResource::form()`; belum ada mekanisme registrasi komponen kustom. |
| Plugin System         | ⬜     | `packages/` masih kosong; belum ada arsitektur plugin. |
| CTA / Team / Feature Section | ⬜ | Tipe blok yang dijanjikan README utama belum dibuat (lihat [docs/api — Tipe Blok](../api/README.md#-tipe-blok-halaman-blocks)). |

---

## 🏢 Phase 6 — Multi Tenant & Marketplace

| Item          | Status | Bukti di Kode                                              |
| ------------- | ------ | ---------------------------------------------------------- |
| Multi Tenant  | ⬜     | Tidak ada isolasi *tenant* — `websites` belum membatasi akses data per pengguna. |
| Marketplace   | ⬜     | Belum ada. |

---

## 🔗 Ketergantungan Antar Fase

```
Phase 1 (auth/RBAC) ──┬──▶ Phase 2 (website/pages)
                      │         │
                      │         ├──▶ Phase 3 (media/blog/SEO)
                      │         │         │
                      │         └──▶ Phase 4 (API/search/queue)
                      │                     │
                      └─────────────────────┴──▶ Phase 5 (visual builder/plugin)
                                                    │
                                                    ▼
                                              Phase 6 (multi tenant/marketplace)
```

---

## 🎯 Prioritas Lanjutan

Urutan rekomendasi dengan mempertimbangkan *dependency* & dampak:

1. **Ekspos field post & SEO di API publik** — `PostResource` + `PageResource` harus menambah `featured_image`, `excerpt`, `categories`, `author`, dan `seoMeta`. Ini *quick win* besar untuk frontend.
2. **Paginasi + caching API** — `GET /pages` & `GET /posts` saat ini mengembalikan seluruh baris; akan jadi masalah saat konten tumbuh.
3. **Pencarian (Meilisearch/Scout)** — aktifkan Phase 4; gunakan untuk blog & media.
4. **Version history** pada Page/Post — fitur yang dijanjikan README sejak Phase 2.
5. **Frontend publik (`apps/website`)** — masih kosong; tanpa ini CMS ini belum bisa dipakai *end-to-end*.
6. **API tulis + API key/rate limiting** — sebelum membuka integrasi pihak ketiga.
7. **Visual builder** — setelah kontrak blok & frontend sudah stabil.

---

## 📅 Milestone & Rilis

Belum ada *tag* rilis resmi (`v0.1` dst.) maupun target tanggal di repo. Status proyek masih **"under active development"** sesuai README utama. Riwayat perubahan bisa diikuti dari:

- `apps/api/CHANGELOG.md` — log perubahan backend,
- Git history dengan format commit `[KODE_TASK] | tipe(domain): penjelasan` (lihat [CONTRIBUTING.md](../../CONTRIBUTING.md)).
