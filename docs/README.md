# Documentation Directory

**Tanggal Update Terakhir:** 6 Oktober 2026

Folder ini adalah pusat sumber informasi (*Single Source of Truth*) untuk segala hal terkait perancangan, arsitektur, dan kebutuhan bisnis proyek NovaCMS. 

> **Wajib dibaca oleh:** Project Manager, System Analyst, UI/UX Designer, dan Developer.

## 📂 Struktur Dokumen

| Folder / File          | Isi                                                                                              | Status      |
| ---------------------- | ------------------------------------------------------------------------------------------------ | ----------- |
| [`/prd`](./prd)        | *Product Requirement Documents*: fitur (*User Stories*, *Acceptance Criteria*), target *user*, batasan (*scope*). Buku suci saat *coding* agar tidak melenceng. | ⬜ Kosong — folder belum dibuat |
| [`/architecture`](./architecture/README.md) | Diagram arsitektur sistem, *layout* monorepo, *stack* teknologi, pola desain, alur logika, dan cara menjalankan aplikasi. | ✅ Tersedia  |
| [`/database`](./database/README.md) | Desain skema database, Entity Relationship Diagram (ERD), kamus data (*Data Dictionary*), dan sejarah migrasi. | ✅ Tersedia  |
| [`/api`](./api/README.md) | Dokumentasi *end-points* & *API contracts* publik v1 (path, *query param*, *response*, *status code*, aturan filter). Harus selalu akurat dan ter-*update* setiap ada perubahan *request/response* di *backend*. | ✅ Tersedia  |
| [`/roadmap`](./roadmap/README.md) | Timeline penyelesaian proyek secara makro (Phase 1–6), status per fitur, target rilis (*milestones*), dan *backlog*. | ✅ Tersedia  |

Peta navigasi singkat:

- Butuh membuat/membaca konten dari luar? → [`/api`](./api/README.md)
- Butuh tahu skema & relasi tabel? → [`/database`](./database/README.md)
- Baru *onboarding* / butuh cara menjalankan? → [`/architecture`](./architecture/README.md)
- Butuh tahu apa yang sudah/belum dibangun? → [`/roadmap`](./roadmap/README.md)

## ✍️ Aturan Dokumentasi

- Gunakan format **Markdown (.md)** agar mudah dirender dan dibaca di repositori (GitHub).
- Selalu cantumkan **Tanggal Update Terakhir** di bagian atas setiap dokumen.
- Bahasa Indonesia untuk seluruh dokumen, konsisten dengan bahasa di komentar & *commit message* proyek.
- Untuk perubahan *backend* yang mengubah *request/response* API, **wajib** memperbarui [`/api`](./api/README.md) dalam commit yang sama.
- Ingat bahwa semua aktivitas mengacu ke standar di **[CONTRIBUTING.md](../CONTRIBUTING.md)**.

## 🧭 Format Commit Dokumentasi

Sesuai [CONTRIBUTING.md](../CONTRIBUTING.md), *commit* dokumentasi memakai format:

```
[KODE_TASK] | docs([domain]): [kata kerja + penjelasan]
```

Contoh: `NOVA-4 | docs(api): menambahkan dokumentasi endpoint publik v1`
