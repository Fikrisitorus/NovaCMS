# Dokumentasi API Publik v1

**Tanggal Update Terakhir:** 6 Oktober 2026

> **Wajib dibaca oleh:** Frontend Developer, Mobile Developer, dan Backend Developer.
> Dokumen ini adalah *contract* resmi endpoint publik NovaCMS. Setiap perubahan `request`/`response` di backend **wajib** diperbarui di sini.

---

## 📖 Pengantar

NovaCMS adalah *headless CMS*: seluruh konten dikelola melalui *admin panel* Filament, lalu disajikan ke *frontend* melalui **REST API publik yang bersifat *read-only*** (hanya method `GET`).

Endpoint publik digunakan oleh:

- Frontend website (`apps/website` — belum tersedia),
- Template/tema kustom buatan developer,
- Aplikasi *mobile* atau integrasi pihak ketiga yang hanya butuh membaca konten.

Semua *endpoint* tidak memerlukan autentikasi, token, maupun API key. Data sensitif (post *draft*, halaman belum terbit, website non-aktif, *field* internal) tidak pernah diekspos.

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
| Autentikasi           | Tidak ada. Semua *endpoint* bersifat publik.                                                                                                                     |
| Paginasi              | **Tersedia** untuk `GET /posts` (15/halaman), `GET /categories/{slug}/posts` (15/halaman), dan `GET /media` (24/halaman) — mengikuti format paginasi standar Laravel (`data`, `links`, `meta`). `GET /pages` & `GET /websites` masih mengembalikan seluruh baris. |
| *Sorting*             | Mengikuti *default* *backend* (lihat tiap *endpoint*). Tidak ada parameter `sort`/`order`.                                                                       |
| Pencarian             | **Belum tersedia** di API publik. Pencarian hanya ada di kolom tabel *admin panel*.                                                                               |
| Rate limiting         | **Belum diterapkan.**                                                                                                                                              |

---

## 📋 Daftar Endpoint

| Method | Path                  | Deskripsi                                                      |
| ------ | --------------------- | -------------------------------------------------------------- |
| `GET`  | `/websites`           | Daftar seluruh website aktif.                                   |
| `GET`  | `/websites/{domain}`  | Detail satu website + daftar halaman terbitnya.                 |
| `GET`  | `/pages`              | Daftar seluruh halaman terbit (bisa difilter per website).      |
| `GET`  | `/pages/{slug}`       | Detail satu halaman beserta konten `blocks`.                    |
| `GET`  | `/posts`              | Daftar post blog terbit, paginasi 15 per halaman.               |
| `GET`  | `/posts/{slug}`       | Detail satu post blog beserta author, categories, seo_meta.     |
| `GET`  | `/categories`         | Daftar seluruh kategori lengkap dengan jumlah post terbitnya.   |
| `GET`  | `/categories/{slug}/posts` | Post terbit dalam satu kategori, paginasi 15 per halaman.  |
| `GET`  | `/media`              | Daftar media library, paginasi 24 per halaman.                  |

Path di atas adalah path relatif terhadap `/api/v1`. Contoh lengkap: `GET http://localhost:8000/api/v1/pages/tentang-kami`.

---

## 🌐 Website

### `GET /websites`

Mengembalikan daftar **seluruh website yang aktif** (`is_active = true`), diurutkan dari yang terbaru dibuat.

**Query parameter:** tidak ada.

**Request**

```bash
curl -sS http://localhost:8000/api/v1/websites
```

**Response `200 OK`**

```jsonc
{
  "data": [
    {
      "id": "0192a3b4-c5d6-7e8f-9012-3456789abcde",
      "name": "Situs Utama",
      "domain": "example.com",
      "is_active": true,
      "created_at": "2026-09-01T08:00:00.000000Z",
      "updated_at": "2026-10-05T14:20:00.000000Z"
    },
    {
      "id": "0192a3b4-1111-2222-3333-444455556666",
      "name": "Blog Produk",
      "domain": "blog.example.com",
      "is_active": true,
      "created_at": "2026-08-12T08:00:00.000000Z",
      "updated_at": "2026-09-30T09:15:00.000000Z"
    }
  ]
}
```

> Catatan: *endpoint* ini **tidak** menyertakan daftar `pages`. Gunakan `GET /websites/{domain}` atau `GET /pages?website_id=...` bila butuh halamannya.

---

### `GET /websites/{domain}`

Mengembalikan detail satu website berdasarkan **domain** (bukan UUID), sekaligus daftar **halaman yang sudah terbit** milik website tersebut.

| Path Parameter | Wajib | Tipe   | Keterangan                                            |
| -------------- | ----- | ------ | ----------------------------------------------------- |
| `domain`       | ✅     | string | Nilai kolom `websites.domain`, contoh: `example.com`.  |

**Request**

```bash
curl -sS http://localhost:8000/api/v1/websites/example.com
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

Mengembalikan daftar **seluruh halaman yang sudah terbit** (`is_published = true`) dari seluruh website, diurutkan dari yang terbaru dibuat, lengkap dengan data website induknya.

**Query parameter**

| Parameter     | Wajib | Tipe  | Keterangan                                                                                     |
| ------------- | ----- | ----- | ---------------------------------------------------------------------------------------------- |
| `website_id`  | ❌     | UUID  | Bila diisi, hanya mengembalikan halaman milik website (UUID) tersebut.                          |

**Request**

```bash
# Semua halaman
curl -sS http://localhost:8000/api/v1/pages

# Hanya halaman satu website
curl -sS "http://localhost:8000/api/v1/pages?website_id=0192a3b4-c5d6-7e8f-9012-3456789abcde"
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
| `slug`         | ✅     | string | Nilai kolom `pages.slug`, bersifat unik di seluruh aplikasi. |

**Request**

```bash
curl -sS http://localhost:8000/api/v1/pages/tentang-kami
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

Mengembalikan daftar **post yang sudah dipublikasikan**, yaitu: `is_published = true` **dan** `published_at <= sekarang`. Diurutkan berdasarkan tanggal publikasi terbaru.

**Query parameter:** tidak ada.

**Request**

```bash
curl -sS http://localhost:8000/api/v1/posts
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
| `slug`         | ✅     | string | Nilai kolom `posts.slug`, bersifat unik di seluruh aplikasi. |

**Request**

```bash
curl -sS http://localhost:8000/api/v1/posts/memperkenalkan-novacms
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
| `404`  | Not Found            | Resource tidak ada, **atau** resource ada tetapi gagal aturan filter publik (*draft* / terjadwal / website non-aktif). |
| `405`  | Method Not Allowed   | Memakai method selain `GET` (mis. `POST /api/v1/pages`).                                             |
| `429`  | Too Many Requests    | (Disiapkan) *Rate limiting* belum diterapkan saat ini.                                               |
| `500`  | Internal Server Error| Kesalahan server. Jika `APP_DEBUG=true`, body berisi *stack trace* — jangan ekspos ke produksi.      |

Format *error response* mengikuti *default* Laravel:

```json
{
  "message": "No query results for model [App\\Models\\Page]."
}
```

---

## ⚠️ Gap & Catatan Implementasi

Hal-hal yang **belum ada** di API publik per 6 Oktober 2026, agar *consumer* tidak berharap lebih:

1. **`GET /pages` dan `GET /websites` belum dipaginasi** (masih mengembalikan seluruh baris). `GET /posts`, `GET /categories/{slug}/posts`, dan `GET /media` sudah dipaginasi.
2. **Belum ada endpoint untuk `seoMeta` mandiri** dan `websites/{id}` — SEO meta sudah ikut di response post/page, tapi belum ada endpoint khusus.
3. **Tidak ada pencarian / filter** selain `?website_id=` pada `/pages` (tidak ada filter `?domain=`, `?category=`, `?q=`).
4. **Tidak ada caching** (`Cache::remember`) maupun ETag di *endpoint*, padahal konten publik jarang berubah.
5. **Tidak ada API key / rate limiting**, sehingga siapa saja bisa membaca seluruh konten publik tanpa batas.
6. **Tidak ada dokumentasi OpenAPI/Swagger** maupun koleksi Postman; dokumen ini satu-satunya *contract*.

---

## 🧪 Pengujian

Regresi aturan filter di atas dilindungi oleh *test suite* `apps/api/tests/Feature/ApiPagePostPublishTest.php`, yang menguji:

- `GET /pages/{slug}` mengembalikan `blocks` dan tidak *crash* akibat relasi `sections` lama,
- `GET /pages/{slug}` → `404` untuk halaman *draft*,
- `GET /posts` hanya menampilkan post yang sudah benar-benar terbit (bukan *unpublished*, bukan terjadwal),
- `GET /posts/{slug}` → `404` untuk *unpublished* maupun terjadwal.

Jalankan sebelum menyentuh *endpoint*:

```bash
cd apps/api && php artisan test
```
