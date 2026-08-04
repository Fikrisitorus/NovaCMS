# Documentation Directory

Folder ini adalah pusat sumber informasi (*Single Source of Truth*) untuk segala hal terkait perancangan, arsitektur, dan kebutuhan bisnis proyek NovaCMS. 

> **Wajib dibaca oleh:** Project Manager, System Analyst, UI/UX Designer, dan Developer.

## 📂 Struktur Dokumen

- **`/prd` (Product Requirement Documents)**: Berisi penjelasan rinci fitur (*User Stories*, *Acceptance Criteria*), target *user*, dan batasan (*scope*) dari produk. Ini adalah buku suci saat coding agar tidak melenceng.
- **`/architecture`**: Berisi diagram arsitektur sistem (contoh: interaksi API dan Frontend), alur logika aplikasi (*flowcharts*), dan strategi *deployment* server (Cloud/VPS).
- **`/database`**: Berisi desain skema database, Entity Relationship Diagram (ERD), kamus data (*Data Dictionary*), dan sejarah panjang migrasi jika diperlukan.
- **`/api`**: Dokumentasi *end-points*, *API contracts*, dan koleksi postman/swagger. Harus selalu akurat dan ter-update setiap ada perubahan *request/response* di backend.
- **`/roadmap`**: Timeline penyelesaian proyek secara makro, target rilis (*Milestones*), dan fitur-fitur masa depan (*backlogs*).

## ✍️ Aturan Dokumentasi
- Gunakan format **Markdown (.md)** agar mudah dirender dan dibaca di repositori (GitHub).
- Selalu cantumkan **Tanggal Update Terakhir** di bagian atas setiap dokumen.
- Ingat bahwa semua aktivitas mengacu ke standar di **[CONTRIBUTING.md](../../CONTRIBUTING.md)**.
