# NovaCMS

Selamat datang di repositori NovaCMS. Proyek ini menggunakan arsitektur monorepo yang berisi backend API, frontend website, dokumentasi, dan reusable packages.

## 🌳 Git Workflow & Commit Guidelines

Untuk menjaga kerapian riwayat komit dan mencegah konflik kode, ikuti alur kerja berikut sebelum dan sesudah menulis kode:

### Alur Kerja (Workflow)
1. Jalankan `git status` dan pastikan kamu berada di branch `dev` pribadi kamu, misalnya: `DEV_Nama`.
2. Lakukan `git pull origin dev` (atau dari branch integrasi utama) sebelum mulai coding untuk mendapatkan perubahan terbaru.
3. Setelah selesai coding, lakukan *pull* lagi dari branch `dev` utama untuk memastikan tidak ada konflik sebelum commit/push.
4. Sebelum melakukan push, pastikan kamu sudah menjalankan *linter* atau *test* (misal: `php artisan test` untuk Laravel, atau `npm run lint` & `npm run build` untuk Frontend) dan pastikan tidak ada error.
5. Ajukan **Pull Request (PR)** dari branch kamu (`DEV_Nama`) ke branch `dev`.

### Format Commit (Conventional Commits)
Pastikan setiap pesan commit mengikuti format *Conventional Commits* yang dikombinasikan dengan kode tiket tugas (misalnya dari Jira, Linear, atau Trello).

**Format:**
`[KODE_TASK] | [tipe]([domain_opsional]): [kata kerja dan penjelasan perubahan]`

*(Catatan: Jika perubahan mencakup semua domain atau bersifat general, bagian `(domain)` bisa dikosongkan).*

**Tipe Commit yang Disarankan:**
- `feat`: Menambahkan fitur baru.
- `fix`: Memperbaiki bug.
- `chore`: Perawatan rutin, update dependency, hal kecil yang tidak mengubah kode produksi.
- `refactor`: Menulis ulang/merapikan kode tanpa mengubah perilakunya (optimasi).
- `docs`: Perubahan khusus untuk dokumentasi (README, komentar).

**Contoh Implementasi:**
- `NOVA-17 | feat(api): membuat relasi pada model User dan Post`
- `NOVA-18 | fix(website): memperbaiki padding tombol login di layar mobile`
- `NOVA-19 | chore: update library tailwindcss ke versi terbaru`
- `NOVA-20 | docs(api): menambahkan dokumentasi swagger untuk auth`

## 📐 Global Code Style & Guidelines

Semua kontributor **wajib** mematuhi standar penulisan kode berikut agar proyek tetap rapi, mudah dibaca, dan mudah di-maintenance.

### 1. Prinsip Utama (Core Principles)
- **Clean Code**: Gunakan penamaan variabel/fungsi yang deskriptif dan struktur kode yang rapi.
- **KISS (Keep It Simple, Stupid)**: Hindari membuat solusi yang terlalu rumit jika ada cara yang lebih sederhana.
- **DRY (Don't Repeat Yourself)**: Hindari duplikasi. Ekstrak logic yang berulang menjadi fungsi, komponen, atau package terpisah.
- **YAGNI (You Aren't Gonna Need It)**: Jangan menulis kode untuk fitur yang belum benar-benar dibutuhkan saat ini.

### 2. Komentar (Comments)
- Tulis kode yang bisa menjelaskan dirinya sendiri (self-documenting).
- Tambahkan **komentar singkat/panjang yang jelas** untuk logic yang kompleks, algoritma rumit, atau business logic tertentu.

### 3. Jarak Spasi dan Import (Spacing & Imports)
- Beri jarak **1 baris kosong** antara area import third-party/package dan area import local file.
- Beri jarak **2 baris kosong** antara area import secara keseluruhan dengan area kode utama.

**Contoh:**
```javascript
import React from "react"; // ini area import instalasi/third party file/package
// beri 1 baris kosong
import API_URL from "../constant"; // ini area import lokal file
// beri 2 baris kosong
// beri 2 baris kosong
const a = 1; // ini area code
export default function A() { // ini area code juga
    // ...
}
```

---
Silakan merujuk ke folder `docs/`, `apps/`, dan `packages/` untuk melihat detail spesifik tiap bagian.