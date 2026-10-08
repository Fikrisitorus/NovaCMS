# Dokumentasi API Publik v1

**Tanggal Update Terakhir:** 7 Oktober 2026

> **Wajib dibaca oleh:** Frontend Developer, Mobile Developer, dan Backend Developer.
> Dokumen ini adalah *contract* resmi endpoint publik NovaCMS. Setiap perubahan `request`/`response` di backend **wajib** diperbarui di sini.

---

## 📖 Pengantar

NovaCMS adalah *headless CMS*: seluruh konten dikelola melalui *admin panel* Filament, lalu disajikan ke *frontend* melalui **REST API publik yang bersifat *read-only*** (hanya method `GET`).

Endpoint publik digunakan oleh:

- Frontend website (`apps/website`),
- Template/tema kustom buatan developer,
- Aplikasi *mobile* atau integrasi pihak ketiga yang hanya butuh membaca konten.

Semua *endpoint* memerlukan **kunci API** yang terikat ke satu website (lihat [🔐 Autentikasi & Rate Limit](#-autentikasi--rate-limit)). Data sensitif (post *draft*, halaman belum terbit, website non-aktif, *field* internal) tidak pernah diekspos.

---

## 🔗 Base URL & Prefix

```
{APP_URL}/api/v1
```

| Lingkungan | Base URL                       |
| ---------- | ------------------------------ |
| Lokal      | `http://localhost:8000/api/v1` |
| Docker     | `http://localhost:8000/api/v1` |
| Herd       | `http://novacms.test/api/v1`   |

Prefix `/api` ditambahkan otomatis oleh Laravel (file `routes/api.php`), dan prefix `/v1` didefinisikan eksplisit di dalam route group. Versi API berikutnya kelak menggunakan prefix `/v2` agar tidak memutus *consumer* lama.

---

## 📐 Konvensi Response

| Aspek                 | Keterangan                                                                                                                                                       |
| --------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Pembungkus            | Selalu dibungkus di bawah kunci `data`. Koleksi → `data` berupa *array*. Item tunggal → `data` berupa *object*.                                                  |
| Tipe data ID          | UUID v4 string, contoh: `0192a3b4-c5d6-7e8f-9012-3456789abcde`.                                                                                                   |
| Format *timestamp*    | ISO 8601 dengan zona waktu UTC, contoh: `2026-10-06T10:30:00.000000Z`.                                                                                            |
| Format *boolean*      | `true` / `false` (JSON asli, bukan 0/1).                                                                                                                          |
| *Null*                | Field yang boleh kosong (`blocks`, `content`) tetap muncul dengan nilai `null`.                                                                                  |
| Filter default        | Hanya konten yang "sudah terbit" (lihat [Aturan Filter](#-aturan-filter--visibilitas-publik)).                                                                   |
| Autentikasi           | **Kunci API wajib** untuk seluruh *endpoint* — header `Authorization: Bearer <key>` atau query `?api_key=<key>`. Kunci terikat ke satu website (lihat [🔐 Autentikasi & Rate Limit](#-autentikasi--rate-limit)). |
| Isolasi tenant        | Kunci hanya bisa membaca **website pemilik kunci**. `GET /websites` selalu mengembalikan satu website (pemilik kunci); *endpoint* lain memfilter konten miliknya. Data tenant lain tidak pernah muncul. |
| Paginasi              | **Tersedia** untuk `GET /posts` (15/halaman), `GET /categories/{slug}/posts` (15/halaman), dan `GET /media` (24/halaman) — mengikuti format paginasi standar Laravel (`data`, `links`, `meta`). `GET /pages` & `GET /websites` masih mengembalikan seluruh baris milik tenant. |
| *Sorting*             | Mengikuti *default* *backend* (lihat tiap *endpoint*). Tidak ada parameter `sort`/`order`.                                                                       |
| Pencarian             | **Tersedia** via parameter `?q=` pada `/posts`, `/pages`, `/media`, dan `/categories/{slug}/posts` (lihat [🔍 Pencarian](#-pencarian-q)).                       |
| Rate limiting         | **60 request/menit per kunci.** Lihat [🔐 Autentikasi & Rate Limit](#-autentikasi--rate-limit).                                                                  |

---

## 📋 Daftar Endpoint

Semua *endpoint* memerlukan kunci API (lihat [🔐 Autentikasi & Rate Limit](#-autentikasi--rate-limit)). Contoh *request* di bawah menggunakan header `Authorization`; ganti `<KEY>` dengan kunci API Anda.

| Method | Path                       | Deskripsi                                                      |
| ------ | -------------------------- | -------------------------------------------------------------- |
| `GET`  | `/websites`                | Website pemilik kunci + daftar halaman terbitnya.              |
| `GET`  | `/websites/{domain}`       | Detail website pemilik kunci (404 untuk domain tenant lain).   |
| `GET`  | `/pages`                   | Daftar halaman terbit milik tenant (mendukung `?q=`).          |
| `GET`  | `/pages/{slug}`            | Detail satu halaman beserta konten `blocks`.                   |
| `GET`  | `/posts`                   | Daftar post blog terbit, paginasi 15/halaman (mendukung `?q=`). |
| `GET`  | `/posts/{slug}`            | Detail satu post blog beserta author, categories, seo_meta.    |
| `GET`  | `/categories`              | Daftar kategori yang punya post terbit milik tenant + jumlah.  |
| `GET`  | `/categories/{slug}/posts` | Post terbit dalam satu kategori, paginasi 15/halaman (`?q=`).  |
| `GET`  | `/media`                   | Daftar media milik tenant, paginasi 24/halaman (`?q=`).        |

Path di atas adalah path relatif terhadap `/api/v1`. Contoh lengkap: `GET http://localhost:8000/api/v1/pages/tentang-kami`.

---

## 🔐 Autentikasi & Rate Limit

### Kunci API

Setiap *request* **wajib** menyertakan kunci API yang diterbitkan untuk satu website. Kunci bisa diletakkan di *header* atau di *query string*:

```bash
# Direkomendasikan: header Authorization (tidak tercatat di URL/server log)
curl -sS -H "Authorization: Bearer novacms_xxxxxxxxxxxxxxxxxxxxxxxx" \
  http://localhost:8000/api/v1/posts

# Alternatif: query string
curl -sS "http://localhost:8000/api/v1/posts?api_key=novacms_xxxxxxxxxxxxxxxxxxxxxxxx"
```

Kunci berformat `novacms_<random 40 karakter>` dan disimpan di tabel `api_keys`. Satu website bisa memiliki banyak kunci — mis. terpisah untuk *production* dan *staging* — dan masing-masing dapat **dicabut** (`revoked_at`) tanpa menghapus baris, sehingga audit trail tetap utuh. Mencabut kunci seketika memutus akses frontend yang memakainya.

**Membuat kunci** (admin panel → *Kunci API* → *Buat kunci API*, atau CLI):

```bash
php artisan api-key:create {domain-website} "Production"
php artisan api-key:revoke novacms_xxx          # mencabut kunci
php artisan api-key:flush-usage                  # dipanggil scheduler tiap jam
```

> ⚠️ **Kunci penuh hanya ditampilkan sekali** saat dibuat (notifikasi di panel / output CLI). Simpan langsung ke *secret manager* atau `.env` frontend — tidak bisa diambil ulang dari UI.

### Isolasi multi-tenant

Kunci terikat ke satu `website_id`. Semua *endpoint* hanya mengembalikan data milik website tersebut:

- `GET /websites` selalu mengembalikan **satu** website (pemilik kunci), bukan daftar seluruh tenant.
- `GET /websites/{domain}` mengembalikan 404 bila domain bukan milik tenant.
- `GET /posts`, `GET /pages`, `GET /media`, `GET /categories` hanya memfilter konten milik tenant. Tabel `categories` dan `media` tidak punya `website_id` langsung — isolasi kategori lewat relasi *posts*, media lewat kolom `media.website_id`.

Nonaktifkan website (`is_active = false`) seketika memutus **semua** kuncinya (403) tanpa harus mencabut kunci satu per satu.

### Rate limiting

| Aspek             | Nilai                        |
| ----------------- | ---------------------------- |
| Batas             | **60 request / menit per kunci** |
| Jendela           | Tetap (*fixed window*) 60 detik |
| Cakupan           | Per kunci API (bukan per IP)  |

Melebihi batas mengembalikan `429`:

```json
{
  "message": "Terlalu banyak request. Coba lagi nanti.",
  "retry_after": 37
}
```

Setiap *response* menyertakan *header* standar agar frontend bisa menampilkan UI *throttle*:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1762247400
Retry-After: 37          # hanya saat 429
```

**Penanganan di frontend:** saat menerima `429`, jangan langsung *retry*. Baca `Retry-After`, tunggu, lalu coba lagi. Implementasi frontend yang baik juga memantau `X-RateLimit-Remaining` untuk *debounce* proaktif.

---

## 🔍 Pencarian (`?q=`)

Empat *endpoint* mendukung pencarian *case-insensitive* via parameter `?q=`:

| Endpoint                          | Kolom yang dicari                              |
| --------------------------------- | ---------------------------------------------- |
| `GET /posts`                      | `title`, `excerpt`, `content`                  |
| `GET /categories/{slug}/posts`    | `title`, `excerpt`, `content`                  |
| `GET /pages`                      | `title`, `slug`                                |
| `GET /media`                      | `name`, `alt_text`, `caption`, `file_name`     |

```bash
curl -sS -H "Authorization: Bearer <KEY>" \
  "http://localhost:8000/api/v1/posts?q=laravel"
```

- Pencarian memakai `LIKE %q%` (portabel SQLite/PostgreSQL) — mendukung *substring*, bukan *full-text search*. Upgrade ke Meilisearch/Scout kelak akan mengganti layer ini tanpa mengubah kontrak `?q=`.
- `?q=` kosong / tidak diisi = tanpa filter (mengembalikan semua).
- Pencarian tetap menghormati aturan filter publik (post *draft*/terjadwal tidak pernah muncul meski cocok).
- Hasil tetap dipaginasi seperti biasa.

---

## 🌐 Website

### `GET /websites`

Mengembalikan **website pemilik kunci API** beserta daftar **halaman yang sudah terbit**. Karena isolasi tenant, selalu tepat satu website — bukan daftar seluruh tenant.

**Query parameter:** tidak ada.

**Request**

```bash
curl -sS -H "Authorization: Bearer <KEY>" http://localhost:8000/api/v1/websites
```

**Response `200 OK`**

```jsonc
{
  "data": {
    "id": "0192a3b4-c5d6-7e8f-9012-3456789abcde",
    "name": "Situs Utama",
    "domain": "example.com",
    "is_active": true,
    "created_at": "2026-09-01T08:00:00.000000Z",
    "updated_at": "2026-10-05T14:20:00.000000Z"
  }
}
```

**Response `403 Forbidden`** — website pemilik kunci sudah dinonaktifkan:

```json
{
  "message": "Website pemilik kunci ini sudah dinonaktifkan."
}
```

> Catatan: `data` selalu berupa **object** tunggal (bukan array). Butuh halamannya? Field `pages` sudah ikut di `GET /websites/{domain}`; atau pakai `GET /pages`.

---

### `GET /websites/{domain}`

Mengembalikan detail website pemilik kunci API berdasarkan **domain** (bukan UUID), sekaligus daftar **halaman yang sudah terbit** milik website tersebut. Domain tenant lain mengembalikan `404` (isolasi tenant).

| Path Parameter | Wajib | Tipe   | Keterangan                                            |
| -------------- | ----- | ------ | ----------------------------------------------------- |
| `domain`       | ✅     | string | Nilai kolom `websites.domain`, contoh: `example.com`.  |

**Request**

```bash
curl -sS -H "Authorization: Bearer <KEY>" http://localhost:8000/api/v1/websites/example.com
```

**Response `200 OK`**

```jsonc
{
  "data": {
    "id": "0192a3b4-c5d6-7e8f-9012-3456789abcde",
    "name": "Situs Utama",
    "domain": "example.com",
    "is_active": true,
    "pages": [
      {
        "id": "0192a3b4-aaaa-bbbb-cccc-ddddeeeeffff",
        "website_id": "0192a3b4-c5d6-7e8f-9012-3456789abcde",
        "title": "Beranda",
        "slug": "beranda",
        "is_published": true,
        "blocks": [
          {
            "type": "hero",
            "data": {
              "heading": "Selamat Datang di NovaCMS",
              "subheading": "Headless CMS modern untuk website Anda",
              "button_label": "Hubungi Kami",
              "button_url": "https://example.com/kontak",
              "background_image": "hero/bg-utama.jpg"
            }
          }
        ],
        "created_at": "2026-09-01T08:00:00.000000Z",
        "updated_at": "2026-10-05T14:20:00.000000Z"
      }
    ],
    "created_at": "2026-09-01T08:00:00.000000Z",
    "updated_at": "2026-10-05T14:20:00.000000Z"
  }
}
```

**Response `404 Not Found`** — domain tidak ditemukan atau website sedang non-aktif.

```json
{
  "message": "No query results for model [App\\Models\\Website]."
}
```

---

## 📄 Page

### `GET /pages`

Mengembalikan daftar **halaman yang sudah terbit** (`is_published = true`) milik website pemilik kunci API, diurutkan dari yang terbaru dibuat, lengkap dengan data website induknya.

**Query parameter**

| Parameter | Wajib | Tipe  | Keterangan                                                                                      |
| --------- | ----- | ----- | ----------------------------------------------------------------------------------------------- |
| `q`       | ❌     | string | Pencarian *case-insensitive* pada kolom `title` dan `slug` (lihat [🔍 Pencarian](#-pencarian-q)). |

**Request**

```bash
curl -sS -H "Authorization: Bearer <KEY>" http://localhost:8000/api/v1/pages
```

**Response `200 OK`**

```jsonc
{
  "data": [
    {
      "id": "0192a3b4-aaaa-bbbb-cccc-ddddeeeeffff",
      "website_id": "0192a3b4-c5d6-7e8f-9012-3456789abcde",
      "title": "Tentang Kami",
      "slug": "tentang-kami",
      "is_published": true,
      "blocks": [
        {
          "type": "hero",
          "data": {
            "heading": "Tentang Kami",
            "subheading": "Tim kecil dengan mimpi besar",
            "button_label": null,
            "button_url": null,
            "background_image": null
          }
        },
        {
          "type": "faq",
          "data": {
            "questions": [
              { "question": "Di mana kalian berlokasi?", "answer": "Jakarta, Indonesia." },
              { "question": "Bagaimana cara menghubungi?", "answer": "Email halo@example.com." }
            ]
          }
        }
      ],
      "website": {
        "id": "0192a3b4-c5d6-7e8f-9012-3456789abcde",
        "name": "Situs Utama",
        "domain": "example.com",
        "is_active": true,
        "created_at": "2026-09-01T08:00:00.000000Z",
        "updated_at": "2026-10-05T14:20:00.000000Z"
      },
      "created_at": "2026-09-02T10:00:00.000000Z",
      "updated_at": "2026-10-04T16:00:00.000000Z"
    }
  ]
}
```

> Bila website tidak ditemukan / tidak aktif, parameter `website_id` hanya menghasilkan *array* kosong (`"data": []`) — **tidak** menghasilkan *error*.

---

### `GET /pages/{slug}`

Mengembalikan detail satu halaman berdasarkan **slug**, termasuk seluruh kontennya pada kolom `blocks`.

| Path Parameter | Wajib | Tipe   | Keterangan                          |
| -------------- | ----- | ------ | ----------------------------------- |
| `slug`         | ✅     | string | Nilai kolom `pages.slug`, unik di seluruh aplikasi.               |

**Request**

```bash
curl -sS -H "Authorization: Bearer <KEY>" http://localhost:8000/api/v1/pages/tentang-kami
```

**Response `200 OK`**

```jsonc
{
  "data": {
    "id": "0192a3b4-aaaa-bbbb-cccc-ddddeeeeffff",
    "website_id": "0192a3b4-c5d6-7e8f-9012-3456789abcde",
    "title": "Tentang Kami",
    "slug": "tentang-kami",
    "is_published": true,
    "blocks": [
      {
        "type": "pricing",
        "data": {
          "plans": [
            { "name": "Basic", "price": 99000, "features": ["1 website", "Support email"] },
            { "name": "Pro", "price": 199000, "features": ["5 website", "Priority support", "Hapus branding"] }
          ]
        }
      },
      {
        "type": "contact",
        "data": {
          "recipient_email": "halo@example.com",
          "description": "Kirim pertanyaan Anda, kami balas dalam 1×24 jam."
        }
      }
    ],
    "website": {
      "id": "0192a3b4-c5d6-7e8f-9012-3456789abcde",
      "name": "Situs Utama",
      "domain": "example.com",
      "is_active": true,
      "created_at": "2026-09-01T08:00:00.000000Z",
      "updated_at": "2026-10-05T14:20:00.000000Z"
    },
    "created_at": "2026-09-02T10:00:00.000000Z",
    "updated_at": "2026-10-04T16:00:00.000000Z"
  }
}
```

**Response `404 Not Found`** — slug tidak ditemukan atau halaman masih *draft* (`is_published = false`).

```json
{
  "message": "No query results for model [App\\Models\\Page]."
}
```

---

## 📝 Post (Blog)

### `GET /posts`

Mengembalikan daftar **post yang sudah dipublikasikan** milik website pemilik kunci API, yaitu: `is_published = true` **dan** `published_at <= sekarang`. Diurutkan berdasarkan tanggal publikasi terbaru.

**Query parameter**

| Parameter | Wajib | Tipe  | Keterangan                                                                                      |
| --------- | ----- | ----- | ----------------------------------------------------------------------------------------------- |
| `q`       | ❌     | string | Pencarian *case-insensitive* pada `title`, `excerpt`, `content` (lihat [🔍 Pencarian](#-pencarian-q)). |

**Request**

```bash
curl -sS -H "Authorization: Bearer <KEY>" http://localhost:8000/api/v1/posts
```

**Response `200 OK`**

```jsonc
{
  "data": [
    {
      "id": "0192a3b4-9999-8888-7777-666655554444",
      "title": "Memperkenalkan NovaCMS",
      "slug": "memperkenalkan-novacms",
      "content": "<h2>Halo dunia</h2><p>NovaCMS adalah headless CMS...</p>",
      "published_at": "2026-10-01T09:00:00.000000Z",
      "created_at": "2026-09-28T13:00:00.000000Z",
      "updated_at": "2026-10-01T09:00:00.000000Z"
    }
  ]
}
```

> ℹ️ **Catatan:** `GET /posts` dipaginasi 15 per halaman (format paginasi standar Laravel: `data` + `links` + `meta`). `PostResource` juga mengembalikan `excerpt`, `featured_image`, `is_published`, serta relasi `author`, `categories`, dan `seo_meta` ketika di-*eager-load* backend.

---

### `GET /posts/{slug}`

Mengembalikan detail satu post berdasarkan **slug**.

| Path Parameter | Wajib | Tipe   | Keterangan                        |
| -------------- | ----- | ------ | --------------------------------- |
| `slug`         | ✅     | string | Nilai kolom `posts.slug`, unik di seluruh aplikasi.              |

**Request**

```bash
curl -sS -H "Authorization: Bearer <KEY>" http://localhost:8000/api/v1/posts/memperkenalkan-novacms
```

**Response `200 OK`**

```jsonc
{
  "data": {
    "id": "0192a3b4-9999-8888-7777-666655554444",
    "title": "Memperkenalkan NovaCMS",
    "slug": "memperkenalkan-novacms",
    "content": "<h2>Halo dunia</h2><p>NovaCMS adalah headless CMS...</p>",
    "published_at": "2026-10-01T09:00:00.000000Z",
    "created_at": "2026-09-28T13:00:00.000000Z",
    "updated_at": "2026-10-01T09:00:00.000000Z"
  }
}
```

**Response `404 Not Found`** — slug tidak ditemukan, post masih *draft*, atau post **terjadwal** (waktu publikasi di masa depan).

```json
{
  "message": "No query results for model [App\\Models\\Post]."
}
```

---

## 🔒 Aturan Filter & Visibilitas Publik

Aturan ini diterapkan di sisi *backend* (lihat `app/Http/Controllers/Api/*`) dan **tidak dapat diganggu gugat** dari sisi klien. Tujuannya: konten yang belum siap tidak pernah bocor ke publik.

| Sumber daya  | Kondisi yang ditampilkan                                                | Kondisi yang disembunyikan (404 / tidak muncul)            |
| ------------ | ------------------------------------------------------------------------ | ----------------------------------------------------------- |
| `Website`    | `is_active = true`                                                       | Website non-aktif (`is_active = false`)                     |
| `Page`       | `is_published = true`                                                    | Halaman *draft* (`is_published = false`)                    |
| `Page` (di `websites/{domain}`)| `is_published = true`                                       | Halaman *draft*                                              |
| `Post`       | `is_published = true` **DAN** `published_at <= now()`                    | Post *draft* **atau** post terjadwal (`published_at > now()`) |

Tiga aturan penting yang sering disalahpahami:

1. **Post butuh dua kondisi sekaligus.** `is_published = true` tetapi `published_at` masih di masa depan **tidak** muncul — ini mendukung fitur *scheduled post* (penjadwalan). Demikian pula `published_at` sudah lewat tetapi `is_published = false` tidak muncul.
2. **Halaman tidak mengenal penjadwalan.** `Page` tidak punya kolom `published_at`; cukup toggle `is_published`.
3. **Penghapusan website otomatis menghapus kontennya.** `pages.website_id` dan `posts.website_id` memakai `ON DELETE CASCADE`, sehingga menghapus *website* juga menghapus halaman/post-nya. Post kehilangan *author*-nya hanya mengosongkan `author_id` (`nullOnDelete`).

---

## 🧱 Tipe Blok Halaman (`blocks`)

Kolom `pages.blocks` adalah **JSON array berurutan** yang merepresentasikan komposisi halaman. Setiap elemen dibuat dari *Filament Builder* di *admin panel* (`app/Filament/Resources/PageResource.php`) dengan struktur:

```jsonc
{
  "type": "string",   // identifier blok
  "data": { }         // payload field blok tersebut
}
```

Urutan *array* = urutan tampilan di halaman. Frontend bertugas me-*render* tiap `type` sesuai komponennya masing-masing.

| `type`      | Label di Admin Panel  | Field pada `data`                                                                  | Wajib                    |
| ----------- | --------------------- | ---------------------------------------------------------------------------------- | ------------------------ |
| `hero`      | Hero Section          | `heading` (string), `subheading` (string), `button_label` (string), `button_url` (URL), `background_image` (path file) | `heading`                |
| `faq`       | FAQ Section           | `questions`: *array* of `{ question, answer }`                                      | `question`, `answer`     |
| `gallery`   | Image Gallery         | `images`: *array* path file (banyak gambar)                                          | —                        |
| `pricing`   | Pricing Section       | `plans`: *array* of `{ name, price (number), features (array of string) }`          | `name`, `price`          |
| `contact`   | Contact Form          | `recipient_email` (email), `description` (string)                                    | `recipient_email`        |

Catatan:

- Nilai field yang tidak diisi **tidak selalu muncul sebagai `null`** — Filament hanya menyimpan *field* yang di-*submit*, sehingga *key* bisa saja absen. Frontend wajib mengecek *key* sebelum dipakai (`blocks?.[0]?.data?.heading`).
- `background_image` dan `images` berupa **path relatif** pada *disk* penyimpanan (mis. `hero/bg-utama.jpg`). Frontend harus menggabungkannya dengan base URL *storage* backend.
- Saat ini belum ada validasi skema JSON di sisi API; konsumsi *field* secara defensif.

---

## 🚦 Status Code

| Status | Nama                 | Kapan terjadi                                                                                     |
| ------ | -------------------- | ------------------------------------------------------------------------------------------------- |
| `200`  | OK                   | *Request* berhasil, *resource* ditemukan.                                                           |
| `401`  | Unauthorized         | Kunci API tidak disertakan atau tidak ditemukan.                                                    |
| `403`  | Forbidden            | Website pemilik kunci sudah dinonaktifkan (`is_active = false`).                                    |
| `404`  | Not Found            | Resource tidak ada, **atau** resource ada tetapi gagal aturan filter publik (*draft* / terjadwal), **atau** resource milik tenant lain (isolasi). |
| `405`  | Method Not Allowed   | Memakai method selain `GET` (mis. `POST /api/v1/pages`).                                             |
| `429`  | Too Many Requests    | Lebih dari 60 request/menit dengan kunci yang sama. Body mengandung `retry_after`; header `Retry-After`. |
| `500`  | Internal Server Error| Kesalahan server. Jika `APP_DEBUG=true`, body berisi *stack trace* — jangan ekspos ke produksi.      |

Format *error response* mengikuti *default* Laravel:

```json
{
  "message": "No query results for model [App\\Models\\Page]."
}
```

---

## ⚠️ Gap & Catatan Implementasi

Hal-hal yang **belum ada** di API publik per 7 Oktober 2026, agar *consumer* tidak berharap lebih:

1. **`GET /pages` dan `GET /websites` belum dipaginasi** (masih mengembalikan seluruh baris milik tenant). `GET /posts`, `GET /categories/{slug}/posts`, dan `GET /media` sudah dipaginasi.
2. **Belum ada endpoint untuk `seoMeta` mandiri** dan `websites/{id}` — SEO meta sudah ikut di response post/page, tapi belum ada endpoint khusus.
3. **Pencarian belum *full-text*.** `?q=` memakai `LIKE %q%` — substring match, bukan relevansi/typo-tolerant. Upgrade ke Meilisearch/Scout ada di roadmap.
4. **Hanya `GET /posts` yang di-cache** (`Cache::remember` 15 menit + invalidasi via `PostObserver`). `/pages`, `/categories`, `/media` belum.
5. **Tidak ada dokumentasi OpenAPI/Swagger** maupun koleksi Postman; dokumen ini satu-satunya *contract*.
6. **Kunci API disimpan plaintext** di tabel `api_keys`. Saat ini aman karena DB berada di infrastruktur yang dikontrol penuh, tapi *hashing* (mis. *hash:* `Hash::make` + lookup atas hash) adalah langkah pengerasan berikutnya.

---

## 🧪 Pengujian

Regresi seluruh perilaku di atas dilindungi oleh *test suite* `apps/api/tests/Feature/`:

| File                              | Yang diuji                                                        |
| --------------------------------- | ----------------------------------------------------------------- |
| `ApiPagePostPublishTest`          | Aturan filter publik: `blocks` tidak *crash*, *draft* → 404, post terbit saja. |
| `ApiPublicEndpointsTest`          | Relasi & paginasi `PostResource`, `posts_count` kategori, field internal media tidak di-expose. |
| `ApiPostCacheTest`                | Cache `GET /posts` diisi dan ter-invalidate saat post disimpan/dihapus. |
| `ApiSearchTest`                   | `?q=` pada post (title/content), case-insensitive, tidak tembus *draft*, `?q=` kosong = semua. |
| `ApiKeyMiddlewareTest`            | 401 tanpa kunci, 200 via header/query, header `X-RateLimit-*`, 429 saat lewat batas, revoke → 401. |
| `ApiTenantIsolationTest`          | Data tenant lain tidak muncul di posts/pages/media/categories, domain lain → 404, website nonaktif → 403. |
| `RevisionTest`                    | Versi konten: update → revision baru, restore mengembalikan konten lama, `user_id` tercatat. |

Jalankan sebelum menyentuh *endpoint*:

```bash
cd apps/api && php artisan test
```
