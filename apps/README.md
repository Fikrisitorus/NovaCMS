# Apps Directory

Folder ini berisi aplikasi-aplikasi utama pembentuk NovaCMS. Kita memisahkan antara *backend* dan *frontend* untuk mendukung arsitektur yang *scalable* dan *decoupled*.

## 📂 Struktur Aplikasi

### `/api` (Backend & CMS Admin)
- **Teknologi**: Laravel 12 + Filament PHP.
- **Peran**: Bertindak sebagai REST API provider untuk frontend dan juga sebagai CMS/Admin Panel (menggunakan Filament) untuk manajemen data *backoffice*.
- **Aturan**: 
  - Pastikan *response* API mengikuti standar JSON (misal menggunakan API Resources Laravel).
  - Business logic yang rumit sebisa mungkin diletakkan di layer *Service* atau *Action*, bukan langsung numpuk di dalam *Controller*.

### `/website` (Public Frontend)
- **Teknologi**: Modern Frontend Framework.
- **Peran**: Halaman publik yang diakses oleh pengguna akhir (user/visitor). 
- **Aturan**:
  - Mengambil data (*consume*) secara eksklusif dari `/api` (Backend).
  - Harus memperhatikan SEO (Search Engine Optimization) dan kecepatan *loading* (*performant*).

## 🚀 Panduan Pengembangan
1. **Isolasi Lingkungan**: Setiap sub-folder (`api` dan `website`) memiliki file `.env`, `package.json` atau `composer.json`, dan *dependency* masing-masing. Jangan pernah menyatukannya.
2. **Global Rules**: Selalu merujuk ke **[CONTRIBUTING.md](../../CONTRIBUTING.md)** untuk mematuhi *Code Style* (KISS, DRY, format Spasi, dll).
3. **Sharing Assets**: Jika ada *logic* atau utilitas yang digunakan bersama, pertimbangkan untuk dipindahkan ke folder `/packages`.
