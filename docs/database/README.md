# Database Design

**Tanggal Update Terakhir:** 6 Oktober 2026

> **Wajib dibaca oleh:** Backend Developer, System Analyst, dan DBA.
> Skema berikut dikumpulkan **langsung dari `apps/api/database/migrations/`**, jadi dokumen ini mencerminkan kondisi *database* yang sebenarnya.

---

## 📖 Pengantar

- **RDBMS default:** PostgreSQL 17 (lihat `docker-compose.yml`, service `pg`).
- **Portabilitas:** seluruh *migration* ditulis tanpa *driver-specific* syntax, sehingga juga berjalan di SQLite (mode *test*) dan MySQL. Unique *index* slug dibuat lewat raw `CREATE UNIQUE INDEX` agar portabel.
- **Primary key:** seluruh tabel domain memakai **UUID** (`use HasUuids` di *model*, kolom `uuid('id')->primary()` di *migration*). Tabel infrastruktur Laravel & Spatie memakai *auto-increment* / *string id* sesuai bawaan paketnya.
- **Naming convention:** `snake_case` untuk tabel & kolom; tabel pivot memakai nama kedua tabel model dalam **urutan alfabetis** (`category_post`).

---

## 🔗 ERD (Entity Relationship)

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│   websites   │ 1     N │    pages     │ N     1 │  seo_metas   │
│──────────────│────────▶│──────────────│─────────│ (polymorph)  │
│ id (uuid) PK │         │ id (uuid) PK │         │──────────────│
│ name         │         │ website_id   │         │ id (uuid) PK │
│ domain UNIQUE│         │ title        │         │ seoable_type │◀──┐
│ is_active    │         │ slug UNIQUE  │         │ seoable_id   │   │
└──────┬───────┘         │ is_published │         │ meta_title   │   │
       │                 │ blocks (JSON)│         │ ...          │   │
       │ 1               └──────────────┘         └──────────────┘   │
       │                          ▲                                  │
       │ N                        │ morphOne (seoable)               │
┌──────┴───────┐         ┌──────────────┐                             │
│    posts     │ N     1 │  categories  │                             │
│──────────────│◀───────│──────────────┘                             │
│ id (uuid) PK │  N   N  │ id (uuid) PK │                             │
│ website_id   │────────▶│ name         │                             │
│ author_id ───┼──┐      │ slug UNIQUE  │                             │
│ title        │  │      │ description  │                             │
│ slug UNIQUE  │  │      └──────────────┘                             │
│ featured_img │  │                          ┌──────────────┐         │
│ excerpt      │  └─────────────────────────▶│    users     │         │
│ content      │        (author_id → users)  │──────────────│         │
│ is_published │                             │ id (uuid) PK │         │
│ published_at │                             │ name         │         │
└──────┬───────┘                             │ email UNIQUE │         │
       │ N:N (category_post)                 │ password     │         │
       └─────────────────────────────────────└──────┬───────┘         │
                                                     │ HasRoles       │
                                          ┌──────────▼───────────┐    │
                                          │  Spatie Permission   │    │
                                          │ roles / permissions  │    │
                                          │ model_has_roles      │    │
                                          │ model_has_permissions│    │
                                          │ role_has_permissions │    │
                                          └──────────────────────┘    │
                                                                      │
    ┌──────────────┐   (tidak ada relasi FK; path disimpan string)     │
    │    media     │                                                   │
    │──────────────│   ┌──────────────┐   seoMeta morphTo ─────────────┘
    │ id (uuid) PK │   │   posts      │   (Page & Post memakai HasSeo)
    │ name         │   │   pages      │
    │ path         │   └──────────────┘
    └──────────────┘
```

---

## 📊 Tabel Domain (Bisnis)

### `websites`

| Kolom         | Tipe           | Constraint                  | Keterangan                                      |
| ------------- | -------------- | --------------------------- | ----------------------------------------------- |
| `id`          | `uuid`         | **PK**                       | Identifikasi unik website.                       |
| `name`        | `varchar(255)` | NOT NULL                     | Nama website, untuk label di *admin panel*.       |
| `domain`      | `varchar(255)` | NOT NULL, **UNIQUE**         | Domain website, dipakai sebagai path parameter API. |
| `is_active`   | `boolean`      | NOT NULL, *default* `true`   | Flag aktif; `false` menyembunyikan website dari API publik. |
| `created_at`  | `timestamp`    | NOT NULL                     | Waktu dibuat.                                     |
| `updated_at`  | `timestamp`    | NOT NULL                     | Waktu diubah.                                     |

**Relasi:** `1 — N` ke `pages`, `1 — N` ke `posts`.

---

### `pages`

| Kolom          | Tipe           | Constraint                              | Keterangan                                     |
| -------------- | -------------- | --------------------------------------- | ---------------------------------------------- |
| `id`           | `uuid`         | **PK**                                   | Identifikasi unik halaman.                      |
| `website_id`   | `uuid`         | NOT NULL, **FK** → `websites.id`, `CASCADE` | Website pemilik halaman.                       |
| `title`        | `varchar(255)` | NOT NULL                                 | Judul halaman.                                  |
| `slug`         | `varchar(255)` | NOT NULL, **UNIQUE** (`pages_slug_unique`) | Slug URL, auto-generate dari `title` saat dibuat dari form Filament. |
| `is_published` | `boolean`      | NOT NULL, *default* `false`              | Flag terbit; `false` = *draft*, disembunyikan dari API publik. |
| `blocks`       | `json`         | NULLABLE                                 | Konten halaman sebagai array blok (lihat [docs/api](../api/README.md#-tipe-blok-halaman-blocks)). |
| `created_at`   | `timestamp`    | NOT NULL                                 | Waktu dibuat.                                   |
| `updated_at`   | `timestamp`    | NOT NULL                                 | Waktu diubah.                                   |

**Relasi:** `N — 1` ke `websites`; `morphOne — 1` ke `seo_metas` (lewat trait `HasSeo`).

> **Riwayat:** semula ada tabel `page_sections` (relasi `sections`) yang dibuat migrasi `2026_08_07_150642`. Migrasi `2026_09_10_094631` **menghapus tabel tersebut** dan menggantinya dengan kolom JSON `blocks`. Relasi `sections` sudah tidak ada.

---

### `posts`

| Kolom            | Tipe           | Constraint                                  | Keterangan                                   |
| ---------------- | -------------- | ------------------------------------------- | -------------------------------------------- |
| `id`             | `uuid`         | **PK**                                       | Identifikasi unik post.                        |
| `website_id`     | `uuid`         | NULLABLE, **FK** → `websites.id`, `CASCADE`   | Website pemilik post (wajib diisi via form).   |
| `author_id`      | `uuid`         | NULLABLE, **FK** → `users.id`, `SET NULL`     | Penulis post; otomatis diisi user yang login di form. |
| `title`          | `varchar(255)` | NOT NULL                                     | Judul post.                                    |
| `slug`           | `varchar(255)` | NOT NULL, **UNIQUE** (`posts_slug_unique`)    | Slug URL, auto-generate dari `title` bila kosong (model `boot`). |
| `featured_image` | `varchar(255)` | NULLABLE                                     | Path gambar utama (diunggah via form, direktori `posts`). |
| `excerpt`        | `text`         | NULLABLE                                     | Ringkasan post.                                |
| `content`        | `text`         | NULLABLE                                     | HTML *rich-text* (Filament RichEditor); disanitasi mews/purifier saat disimpan. |
| `is_published`   | `boolean`      | NOT NULL, *default* `false`                  | Flag terbit.                                   |
| `published_at`   | `timestamp`    | NULLABLE                                     | Tanggal publikasi; mendukung penjadwalan (`published_at > now()` disembunyikan). |
| `created_at`     | `timestamp`    | NOT NULL                                     | Waktu dibuat.                                  |
| `updated_at`     | `timestamp`    | NOT NULL                                     | Waktu diubah.                                  |

**Relasi:** `N — 1` ke `websites`; `N — 1` ke `users` (sebagai `author`); `N — N` ke `categories` lewat `category_post`; `morphOne — 1` ke `seo_metas` (lewat trait `HasSeo`).

---

### `categories`

| Kolom         | Tipe           | Constraint           | Keterangan                                   |
| ------------- | -------------- | -------------------- | -------------------------------------------- |
| `id`          | `uuid`         | **PK**                | Identifikasi unik kategori.                    |
| `name`        | `varchar(255)` | NOT NULL              | Nama kategori.                                 |
| `slug`        | `varchar(255)` | NOT NULL, **UNIQUE**  | Slug, auto-generate dari `name` bila kosong.   |
| `description` | `text`         | NULLABLE              | Deskripsi kategori.                            |
| `created_at`  | `timestamp`    | NOT NULL              | Waktu dibuat.                                  |
| `updated_at`  | `timestamp`    | NOT NULL              | Waktu diubah.                                  |

**Relasi:** `N — N` ke `posts` lewat `category_post`.

---

### `category_post` (pivot)

| Kolom          | Tipe   | Constraint                                        | Keterangan                |
| -------------- | ------ | ------------------------------------------------- | ------------------------- |
| `category_id`  | `uuid` | NOT NULL, **FK** → `categories.id`, `CASCADE`, **PK (gabungan)** | Anggota kategori. |
| `post_id`      | `uuid` | NOT NULL, **FK** → `posts.id`, `CASCADE`, **PK (gabungan)**     | Anggota post.     |

Tidak ada `created_at`/`updated_at` (pivot murni tanpa *timestamp*).

---

### `seo_metas`

| Kolom              | Tipe           | Constraint                     | Keterangan                                        |
| ------------------ | -------------- | ------------------------------ | ------------------------------------------------- |
| `id`               | `uuid`         | **PK**                          | Identifikasi unik metadata.                         |
| `seoable_type`     | `varchar(255)` | NOT NULL, bagian **morph index** | Class model pemilik, mis. `App\Models\Page`.        |
| `seoable_id`       | `uuid`         | NOT NULL, bagian **morph index** | UUID model pemilik.                                  |
| `meta_title`       | `varchar(255)` | NULLABLE                        | Judul untuk `<title>`; *max* 60 karakter di form.    |
| `meta_description` | `text`         | NULLABLE                        | Deskripsi meta; *max* 160 karakter di form.         |
| `og_image`         | `varchar(255)` | NULLABLE                        | Path gambar Open Graph (direktori `seo`).           |
| `canonical_url`    | `varchar(255)` | NULLABLE                        | URL kanonik (mencegah duplikasi konten di SEO).      |
| `meta_keywords`    | `json`         | NULLABLE                        | Array kata kunci (diisi via `TagsInput` di form).    |
| `created_at`       | `timestamp`    | NOT NULL                        | Waktu dibuat.                                       |
| `updated_at`       | `timestamp`    | NOT NULL                        | Waktu diubah.                                       |

**Relasi:** `morphTo` (`seoable`) — *parent* bisa `Page` maupun `Post` lewat trait `HasSeo` (`morphOne`). Index morph: `seoable_type_seoable_id_index`.

---

### `media`

| Kolom        | Tipe             | Constraint                | Keterangan                                        |
| ------------ | ---------------- | ------------------------- | ------------------------------------------------- |
| `id`         | `uuid`           | **PK**                     | Identifikasi unik media.                            |
| `name`       | `varchar(255)`   | NOT NULL                   | Nama media untuk pencarian di *admin panel*.         |
| `file_name`  | `varchar(255)`   | NOT NULL                   | Nama file asli saat diunggah.                       |
| `mime_type`  | `varchar(255)`   | NULLABLE                   | Tipe MIME file.                                     |
| `path`       | `varchar(255)`   | NOT NULL                   | Path relatif pada *disk* (lihat `disk`).             |
| `disk`       | `varchar(255)`   | NOT NULL, *default* `public` | Nama *filesystem disk*: `public` (lokal) atau `s3` (object storage). |
| `size`       | `unsignedBigInteger` | NOT NULL, *default* `0` | Ukuran file dalam byte.                             |
| `alt_text`   | `varchar(255)`   | NULLABLE                   | Teks alternatif (aksesibilitas & SEO gambar).       |
| `caption`    | `varchar(255)`   | NULLABLE                   | Keterangan gambar.                                  |
| `created_at` | `timestamp`      | NOT NULL                   | Waktu dibuat.                                       |
| `updated_at` | `timestamp`      | NOT NULL                   | Waktu diubah.                                       |

**Relasi:** tidak ada foreign key. Path media hanya disimpan sebagai string pada kolom `path` (mis. di `blocks` tipe `gallery` atau `posts.featured_image`). Akses URL: `Media::getUrlAttribute()` → `asset('storage/'.$path)`.

---

### `users`

| Kolom                | Tipe           | Constraint          | Keterangan                                                    |
| -------------------- | -------------- | ------------------- | ------------------------------------------------------------- |
| `id`                 | `uuid`         | **PK**               | Identifikasi unik user.                                         |
| `name`               | `varchar(255)` | NOT NULL             | Nama lengkap.                                                   |
| `email`              | `varchar(255)` | NOT NULL, **UNIQUE** | Email, sekaligus *login identifier*.                            |
| `email_verified_at`  | `timestamp`    | NULLABLE             | Waktu verifikasi email.                                         |
| `password`           | `varchar(255)` | NOT NULL             | *Hash* bcrypt (di-*cast* `hashed` di model).                    |
| `remember_token`     | `varchar(100)` | NULLABLE             | Token "remember me" sesi login.                                 |
| `created_at`         | `timestamp`    | NOT NULL             | Waktu dibuat.                                                   |
| `updated_at`         | `timestamp`    | NOT NULL             | Waktu diubah.                                                   |

**Relasi:** `1 — N` ke `posts` (sebagai `author` via `author_id`); `N — N` ke `roles`/`permissions` lewat Spatie (`HasRoles`).

> Password tidak pernah dikembalikan oleh API (atribut `hidden` di model).

---

## 🏗 Tabel Infrastruktur Laravel

Tabel-tabel berikut dibuat otomatis oleh Laravel, bukan kode domain NovaCMS. Tidak perlu dimodifikasi saat menambah fitur bisnis.

| Tabel                  | Keperluan                          | Catatan kunci                                          |
| ---------------------- | ---------------------------------- | ------------------------------------------------------ |
| `password_reset_tokens`| Token lupa password                | PK `email`; kolom `token`, `created_at`.                 |
| `sessions`             | Penyimpanan sesi (session driver)  | PK `id`; `user_id` (FK ke `users` via `foreignId`), index `last_activity`. |
| `cache`                | Penyimpanan cache                  | PK `key`; `value` mediumText; index `expiration`.         |
| `cache_locks`          | Kunci paralel pada cache atomik    | PK `key`; `owner`, `expiration` (index).                  |
| `jobs`                 | Antrian *queue worker*             | PK `id` (bigint auto); index `queue`.                     |
| `job_batches`          | Antrian batch (job chunking)       | PK `id` (string); pelacakan total/pending/failed jobs.    |
| `failed_jobs`          | Job gagal untuk retry              | `uuid` unique; index gabungan `connection, queue, failed_at`. |

---

## 🛡 Tabel Spatie Permission

Dibuat oleh migrasi `2026_09_10_092324_create_permission_tables.php` (paket `spatie/laravel-permission` ^6.25), dipakai *admin panel* lewat `bezhansalleh/filament-shield`. Nama tabel bisa dikonfigurasi di `config/permission.php`; nilai default dipakai repo.

| Tabel                     | Keperluan                        | Catatan kunci                                                    |
| ------------------------- | -------------------------------- | ---------------------------------------------------------------- |
| `roles`                   | Daftar peran (mis. `super_admin`) | PK `id` bigint auto; unique gabungan `name` + `guard_name`.         |
| `permissions`             | Daftar aksi yang bisa diizinkan  | PK `id` bigint auto; unique gabungan `name` + `guard_name`.         |
| `model_has_roles`         | Relasi role ↔ model (User)        | PK gabungan `role_id` + `model_id` + `model_type`; FK cascade ke `roles`. |
| `model_has_permissions`   | Relasi permission ↔ model         | PK gabungan `permission_id` + `model_id` + `model_type`; FK cascade ke `permissions`. |
| `role_has_permissions`    | Relasi role ↔ permission          | PK gabungan `permission_id` + `role_id`; FK cascade ke `roles` & `permissions`. |

Karena `config/permission.php` mengatur `teams = false`, kolom *team foreign key* **tidak** ikut dibuat.

---

## 📖 Kamus Data Singkat

| Entitas       | Makna / peran di sistem                                                            |
| ------------- | ---------------------------------------------------------------------------------- |
| `websites`    | Satu situs/properti yang dikelola. Satu website punya banyak halaman & post.         |
| `pages`       | Halaman statis satu website; kontennya disusun dari `blocks` JSON (page builder).    |
| `posts`       | Artikel blog; punya penulis, kategori, gambar utama, ringkasan, & penjadwalan terbit. |
| `categories`  | Pengelompokan post; satu post bisa punya banyak kategori.                            |
| `category_post` | Pivot many-to-many antara `posts` & `categories`. Tidak berisi atribut bisnis.     |
| `seo_metas`   | Metadata SEO yang menempel secara polymorphic pada `Page` atau `Post`.               |
| `media`       | Perpustakaan berkas (gambar) yang bisa direferensikan dari mana saja via `path`.      |
| `users`       | Akun admin/staf; login ke panel `/admin` dan bertindak sebagai author post.           |
| `seoable`     | Bukan tabel — nama *alias* polymorphic (`seoable_type` + `seoable_id`) pada `seo_metas`. |

---

## 📜 Sejarah & Catatan Migrasi Penting

| Tanggal    | Migrasi                                          | Dampak                                                                     |
| ---------- | ------------------------------------------------ | --------------------------------------------------------------------------- |
| 2026-08-07 | `create_websites_table`                          | Tabel `websites` (UUID, domain unique).                                      |
| 2026-08-07 | `create_pages_table`                              | Tabel `pages` + FK `website_id` cascade.                                     |
| 2026-08-07 | `create_page_sections_table`                      | **Dihapus kemudian.** Lihat baris 2026-09-10.                                |
| 2026-08-07 | `create_posts_table`                              | Tabel `posts` awal (title, slug, content, published_at).                     |
| 2026-09-10 | `create_permission_tables`                        | Lima tabel Spatie permission.                                                |
| 2026-09-10 | `modify_pages_and_drop_page_sections`             | Menambah `pages.blocks` (JSON) **dan men-drop `page_sections`**. Model `Page` tidak lagi memiliki relasi `sections`. |
| 2026-09-10 | `phase3_create_categories_and_enhance_posts`      | Tabel `categories`, pivot `category_post`, serta kolom baru `posts`: `featured_image`, `excerpt`, `is_published`, `website_id`, `author_id`. |
| 2026-09-10 | `create_seo_metas_table`                          | Tabel `seo_metas` polymorphic.                                               |
| 2026-09-10 | `create_media_table`                              | Tabel `media`.                                                               |
| 2026-10-05 | `add_unique_slug_indexes`                         | Unique index `posts_slug_unique` & `pages_slug_unique`; otomatis menamai ulang slug duplikat menjadi `slug-2`, `slug-3`, dst. agar index bisa dipasang. |

Catatan desain:

- **Tidak ada *soft delete*** (`SoftDeletes`) pada model domain manapun saat ini. Penghapusan bersifat permanen.
- **Tidak ada FK dari `media`** ke tabel lain — relasi media hanya lewat *string path*.
- **Tidak ada index pada `posts.published_at`** padahal endpoint publik mengurutkan & memfilternya; kandidat optimasi saat volume post tumbuh.
- **Unique constraint slug bersifat global** (tidak per-website), jadi dua website tidak boleh memiliki halaman/post dengan slug yang sama.

---

## 🚀 Menghasilkan Skema dari Awal

```bash
cd apps/api

# Lingkungan Docker (otomatis dijalankan saat container pertama kali start)
docker compose up -d

# Manual (lokal / Herd)
php artisan migrate
```

Untuk reset total saat development (hati-hati, menghapus seluruh data):

```bash
php artisan migrate:fresh
```

Regresi skema & aturan filter dilindungi *test suite*: `php artisan test` (lihat `tests/Feature/ApiPagePostPublishTest.php`).
