# Arsitektur NovaCMS

**Tanggal Update Terakhir:** 6 Oktober 2026

> **Wajib dibaca oleh:** Developer baru, System Analyst, dan DevOps.
> Dokumen ini menjelaskan *layout* monorepo, *stack* teknologi, pola desain yang dipakai, serta cara menjalankan proyek.

---

## 📖 Pengantar

NovaCMS adalah **headless CMS**. Lapisan manajemen konten (backend + *admin panel*) dipisahkan total dari lapisan presentasi (frontend), sehingga:

- Konten dikelola di satu tempat, dikonsumsi banyak *frontend*,
- Backend fokus pada **API publik** sebagai *single source of truth*,
- Frontend punya kebebasan penuh memilih teknologi, hosting, dan strategi *rendering*.

---

## 🧱 High-Level Architecture

```
┌────────────────────────────────────────────────────────────────────────┐
│                          NovaCMS Monorepo                              │
│                                                                        │
│  ┌─────────────────────────────────┐   ┌────────────────────────────┐  │
│  │        apps/api (Laravel)        │   │     apps/website           │  │
│  │  ┌────────────┬───────────────┐  │   │  (Public Frontend)        │  │
│  │  │ Filament   │ REST API v1   │  │   │  • Next.js / Nuxt (Rencana)│  │
│  │  │ Admin Panel│ /api/v1/*     │──┼───┼─▶ GET data (read-only)     │  │
│  │  │ /admin     │ (read-only)   │  │   │  • Render blocks + SEO    │  │
│  │  └────────────┴───────────────┘  │   └────────────────────────────┘  │
│  │         Eloquent ORM               │                                │
│  └───────────────┬────────────────────┘                                │
│                  │                                                     │
│  ┌───────────────▼────────────────────────────────────────────────────┐ │
│  │                    Lapisan Data & Infrastruktur                     │ │
│  │  PostgreSQL 17        Redis 7        RustFS (S3)      Mailpit      │ │
│  │  (skema domain)     (cache/queue)   (media library)   (mail test)  │ │
│  └────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────┐                                        │
│   Editor / Content Team      │  →  /admin  (Filament, login email)     │
└──────────────────────────────┘                                        │
┌──────────────────────────────┐                                        │
│       Pengunjung Website     │  →  Frontend publik (apps/website)       │
└──────────────────────────────┘                                        │
```

---

## 🗂 Monorepo Layout

```text
NovaCMS/
├── apps/                        # Aplikasi yang bisa dijalankan
│   ├── api/                     # Laravel: backend + admin panel + API
│   │   ├── app/
│   │   │   ├── Filament/Resources/   # Resource admin panel (CRUD UI)
│   │   │   ├── Http/Controllers/Api/ # Controller endpoint publik
│   │   │   ├── Http/Resources/       # Transformer JSON response
│   │   │   ├── Models/               # Model Eloquent + trait
│   │   │   └── Providers/Filament/   # Konfigurasi panel /admin
│   │   ├── database/migrations/      # Skema database
│   │   ├── config/                    # Konfigurasi (permission, dll)
│   │   ├── routes/                    # api.php, web.php, console.php
│   │   ├── tests/                     # Feature & unit test
│   │   └── composer.json              # Dependency backend terpisah
│   └── website/                # Public frontend (kosong — rencana)
├── packages/                   # Kode terbagi lintas apps (kosong)
├── docs/                       # Dokumentasi (folder ini)
│   ├── api/
│   ├── architecture/
│   │   └── README.md                ← kamu di sini
│   ├── database/
│   ├── prd/                    # Kosong — belum ada dokumen PRD
│   └── roadmap/
├── infrastructure/             # Konfigurasi container
│   └── docker/api/Dockerfile   # Image PHP 8.4 FPM Alpine
├── assets/                     # Aset statis bersama
├── tools/                      # Tooling & script pembantu
├── .github/workflows/          # CI: tests.yml (Pint + PHPUnit)
├── docker-compose.yml          # Orkestrasi seluruh service
├── CONTRIBUTING.md             # Standar kontribusi & format commit
└── README.md                   # Overview proyek
```

### Prinsip pemisahan

| Folder       | Peran                                                                 | Status      |
| ------------ | --------------------------------------------------------------------- | ----------- |
| `apps/api`   | REST API publik + admin panel Filament. Tidak boleh ada logika tampilan publik yang spesifik frontend. | ✅ Aktif    |
| `apps/website` | Frontend publik. **Wajib** mengambil data hanya dari `/api`.            | ⬜ Kosong    |
| `packages`   | Kode terbagi (*shared*) antar aplikasi.                                | ⬜ Kosong    |
| `docs`       | Seluruh dokumentasi sesuai aturan di `docs/README.md`.                 | ✅ Berkembang |
| `infrastructure` | Dockerfile & konfigurasi container.                                 | ✅ Aktif    |

---

## 🛠 Technology Stack

| Lapisan         | Teknologi                        | Versi terkunci / catatan                                    |
| --------------- | -------------------------------- | ----------------------------------------------------------- |
| Bahasa          | **PHP**                          | 8.4 (`infrastructure/docker/api/Dockerfile: php:8.4-fpm-alpine`). |
| Framework       | **Laravel**                      | 13 (composer constraint `laravel/framework ^13.17`).         |
| Admin Panel     | **Filament**                     | v3 (`filament/filament ^3.2`).                               |
| RBAC            | **Spatie laravel-permission**     | ^6.25, diintegrasikan via **filament-shield** ^3.9.           |
| Database        | **PostgreSQL**                   | 17 (`postgres:17-alpine`).                                   |
| Cache & Queue   | **Redis**                        | 7-alpine; driver `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`. |
| Object Storage  | **RustFS**                       | S3-compatible, untuk media library (`FILESYSTEM_DISK=s3`).    |
| Mail Testing    | **Mailpit**                      | Web UI `:8025`, SMTP `:1025`.                                 |
| Code Style      | **Laravel Pint**                 | Preset `laravel`, dijalankan di CI (`.github/workflows/tests.yml`). |
| Testing         | **PHPUnit**                      | ^12.5.12; `apps/api/tests/`.                                  |
| HTML Sanitizer  | **mews/purifier**                | ^3.4; mencegah *stored XSS* pada konten rich-text.            |
| Dev Server      | **Laravel Herd** atau Docker     | Keduanya didukung (lihat [Cara Menjalankan](#-cara-menjalankan)). |

> **Catatan:** README utama menyebut Filament v4 dan beberapa infrastruktur yang belum ada (Meilisearch, Nginx). Yang benar-benar terpasang saat ini adalah **Filament v3**; Meilisearch/Nginx **belum** ada di `docker-compose.yml`. Lihat [Diskrepansi Dokumen](#-diskrepansi-dengan-readme-utama).

---

## 🎨 Pola & Konvensi Desain

### 1. Headless / Content-as-a-Service

- Backend tidak peduli bagaimana konten dirender; frontend tidak peduli bagaimana konten disimpan.
- API publik bersifat **read-only** (lihat [docs/api](../api/README.md)).

### 2. UUID sebagai Identitas

Semua model domain memakai trait `HasUuids`. Manfaat: ID tidak berurutan (*no enumeration attack*), aman dibagikan ke API publik, dan memungkinkan pembuatan ID terlebih dahulu di sisi klien.

### 3. SEO Polymorphic (`HasSeo`)

Trait `App\Models\Traits\HasSeo` memberi relasi `seoMeta(): MorphOne` ke `seo_metas` dengan alias `seoable`. Saat ini dipakai `Page` dan `Post`, sehingga metadata SEO (meta title, description, keywords, OG image, canonical) bisa dipasang pada model apa pun tanpa duplikasi skema.

```php
// Cek apakah ada metadata SEO
$post->seoMeta?->meta_title;
```

### 4. Page Builder via JSON `blocks`

Konten halaman disimpan sebagai **JSON array berurutan** di kolom `pages.blocks` (tipe blok: `hero`, `faq`, `gallery`, `pricing`, `contact`), disusun lewat Filament Builder. Pendekatan ini menggantikan tabel relasional `page_sections` yang lama.

### 5. Defensive Security (Defense in Depth) pada HTML

Dua lapisan perlindungan terhadap *stored XSS*:

- **Saat simpan** — trait `App\Models\Concerns\SanitizesHtml` memurnikan HTML pada saat `saving` (atribut `content`), sehingga database hanya menyimpan HTML bersih.
- **Saat baca** — cast `App\Models\Concerns\HtmlSanitizerCast` memurnikan lagi HTML saat atribut dibaca, mengamani baris yang ditulis sebelum trait ada / di luar model event.

### 6. Resource-based API Response

Setiap *endpoint* mengembalikan `JsonResource` (`WebsiteResource`, `PageResource`, `PostResource`), bukan keluaran *array* mentah, sehingga *shape* JSON konsisten dan terpusat.

### 7. Convention over Configuration

Nama tabel mengikuti jamak *snake_case*; pivot memakai urutan alfabetis (`category_post`); FK memakai `<tabel_singular>_id`; morph memakai `<nama>_type` + `<nama>_id` (`seoable`).

### 8. Repository/Service (panduan, belum diterapkan penuh)

`apps/README.md` meminta *business logic* rumit diletakkan di layer **Service/Action**, bukan menumpuk di *Controller*. Saat ini *controller* API publik masih langsung memanggil Eloquent (masih sederhana, sesuai KISS/YYAGNI).

---

## 🔄 Alur Data Inti

**Membuat halaman baru:**

```
Editor login /admin
  → Filament PageResource form (Page Settings + SEO + Content Builder)
  → slug auto-generate dari title (live/onBlur)
  → toggle is_published
  → blocks disusun via Builder (hero/faq/gallery/pricing/contact)
  → Model::create() → DB: pages + seo_metas (morphOne)
```

**Frontend menampilkan halaman:**

```
apps/website → GET /api/v1/pages/{slug}
  → PageController::showBySlug
  → filter: is_published = true
  → PageResource → JSON { data: { …, blocks: [...] } }
  → frontend me-render tiap block.type
```

**Post terjadwal (*scheduled post*):**

```
Post dibuat: is_published=true, published_at=tanggal depan
  → tidak muncul di API publik (published_at <= now() gagal)
  → tidak muncul di blog Blade /blog
  → otomatis muncul saat published_at terlewati (filter dievaluasi saat request)
```

---

## 🗄 Database & Persistence

- **PostgreSQL 17** sebagai database utama; *migration* tetap portabel ke SQLite (mode *test*) & MySQL.
- Detail skema, ERD, *constraint*, dan kamus data: lihat [docs/database](../database/README.md).
- Media disimpan di *object storage* S3-compatible (RustFS) dengan metadata di tabel `media`.

---

## 🐳 Cara Menjalankan

### Opsi A — Docker Compose (rekomendasi, zero setup)

```bash
# Dari root repo
docker compose up -d
```

Container `api` otomatis menjalankan:

1. `composer install`
2. `php artisan migrate --force`
3. `php artisan storage:link`
4. `php artisan serve --host=0.0.0.0 --port=8000`

Service yang berjalan:

| Service    | Container          | Port                          | Keperluan                     |
| ---------- | ------------------ | ----------------------------- | ----------------------------- |
| api        | `novacms-api`      | `8000` (APP_PORT)             | Laravel + Filament admin      |
| pg         | `novacms-pg`       | `5432` (DB_PORT)              | PostgreSQL 17                 |
| redis      | `novacms-redis`    | `6379` (REDIS_PORT)           | Cache & queue                 |
| mailpit    | `novacms-mailpit`  | `8025` web / `1025` SMTP      | Penangkap email               |
| storage    | `novacms-storage`  | `9000` S3 / `9001` console    | Media library (RustFS)        |

Akses setelah jalan:

- Aplikasi & API: <http://localhost:8000>
- API publik: <http://localhost:8000/api/v1/websites>
- Admin panel: <http://localhost:8000/admin>

Kredensial default Docker (sudah di-*set* via environment `docker-compose.yml`):

```env
DB_CONNECTION=pgsql
DB_HOST=pg
DB_DATABASE=novacms
DB_USERNAME=novacms
DB_PASSWORD=secret

FILESYSTEM_DISK=s3
AWS_ENDPOINT=http://storage:9000
AWS_BUCKET=novacms-media
AWS_ACCESS_KEY_ID=novacms
AWS_SECRET_ACCESS_KEY=novacmssecret
```

Port bisa diubah via variabel lingkungan: `APP_PORT`, `DB_PORT`, `REDIS_PORT`, `MAILPIT_WEB_PORT`, `MAILPIT_SMTP_PORT`, `STORAGE_PORT`, `STORAGE_CONSOLE_PORT`.

### Opsi B — Laravel Herd (lokal, paling cepat untuk development Laravel)

```bash
cd apps/api
composer install
cp .env.example .env       # atau biarkan post-create-project yang lakukan
php artisan key:generate
php artisan migrate
php artisan serve          # atau pakai Herd site: https://herd.dev
```

Untuk menjalankan *queue worker* di latar belakang:

```bash
php artisan queue:work --queue=default
```

### Pengujian

```bash
cd apps/api
php artisan test                            # seluruh test
php artisan test --filter=ApiPagePostPublishTest   # regresi endpoint publik
```

CI (`.github/workflows/tests.yml`) menjalankan **Laravel Pint** (`--preset laravel`) dan **PHPUnit** pada setiap *push*/PR.

---

## 🔐 Keamanan & Batasan Saat Ini

| Aspek                | Status                                                                 |
| -------------------- | ---------------------------------------------------------------------- |
| Autentikasi          | Hanya *admin panel* Filament (email + password, session). API publik tanpa auth. |
| Otorisasi            | Spatie permission via filament-shield; role/permission dikelola di `/admin`. |
| XSS                  | *Double defense*: sanitasi saat simpan (`SanitizesHtml`) + saat baca (`HtmlSanitizerCast`). |
| Visibilitas publik   | Filter `is_active` (website), `is_published` (page), `is_published` + `published_at <= now()` (post). |
| Rate limiting        | ❌ Belum diterapkan pada API publik.                                      |
| CORS                 | Mengikuti *default* Laravel `config/cors.php`; perlu dikonfigurasi saat `apps/website` sudah ada. |
| Audit log            | ❌ Belum ada.                                                             |

---

## ⚠️ Diskrepansi dengan README Utama

README root menjanjikan beberapa hal yang **belum** ada di kode. Tabel ini menjadi rujukan kebenaran:

| Hal di README utama        | Kenyataan di repo                          |
| -------------------------- | ------------------------------------------- |
| Filament v4                | **Filament v3** (`composer.json` `^3.2`)    |
| PostgreSQL + Redis         | ✅ Benar (PostgreSQL 17, Redis 7)            |
| MinIO untuk object storage | **RustFS** (MinIO community berhenti publish image) |
| Meilisearch (Search)       | ❌ Belum ada di `docker-compose.yml`          |
| Nginx                      | ❌ Belum ada; memakai `php artisan serve`     |
| Queue / Cache              | ✅ Konfigurasi Redis sudah ada               |
| Version History            | ❌ Belum ada (tidak ada skema versi)          |
| Activity Log               | ❌ Belum ada                                  |
| Visual Builder             | Sebagian: JSON `blocks` builder sudah ada, builder visual belum |
| Multi Tenant / Marketplace | ❌ Fase 5–6, belum mulai                     |

---

## 📚 Dokumen Terkait

- [docs/api](../api/README.md) — *Contract* endpoint publik v1.
- [docs/database](../database/README.md) — ERD, skema, kamus data.
- [docs/roadmap](../roadmap/README.md) — Status fase & *backlog*.
- [docs/prd](../prd) — Dokumen kebutuhan produk (kosong).
- [CONTRIBUTING.md](../CONTRIBUTING.md) — Standar kontribusi & format commit.
