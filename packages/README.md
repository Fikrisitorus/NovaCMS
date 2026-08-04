# Packages Directory

Folder ini difungsikan untuk menyimpan *internal reusable packages* atau modul-modul yang dipakai secara bersamaan lintas aplikasi.

## 💡 Tujuan
Dengan menggunakan folder packages, kita dapat mematuhi prinsip **DRY (Don't Repeat Yourself)**. Jika ada fungsi utilitas, desain komponen sistem (UI), atau konfigurasi yang digunakan baik di `/api` maupun di `/website`, kode tersebut harus diekstrak dan disimpan di sini.

## 🚀 Panduan
1. Package harus dibuat semodular mungkin (Independent).
2. Terapkan penamaan yang jelas dan pastikan setiap package memiliki fungsi tunggal yang spesifik (Single Responsibility).
3. Selalu perhatikan aturan Spacing & baris kosong (Code Style proyek) saat menulis kode di dalam package.
