# Apps Directory

Folder ini berisi aplikasi-aplikasi utama pembentuk NovaCMS.

## 📂 Struktur Aplikasi

- `/api` - Merupakan core Backend REST API dan Admin Panel. Dibangun menggunakan **Laravel 12 + Filament**.
- `/website` - Merupakan Frontend Public yang diakses oleh user. Dibangun menggunakan teknologi frontend modern (Blade / Inertia / Next.js).

## 🚀 Panduan Pengembangan
1. Setiap sub-folder (`api` dan `website`) memiliki dependency dan konfigurasi masing-masing.
2. Selalu ikuti **Global Code Style** yang ada di `README.md` utama (root proyek).
3. Pastikan tidak ada duplikasi logic antara aplikasi. Jika ada logic yang bisa di-share, pertimbangkan untuk menaruhnya di folder `/packages`.
