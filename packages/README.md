# Packages Directory

Folder ini dikhususkan untuk menyimpan *internal reusable packages* atau modul-modul *agnostic* (berdiri sendiri) yang dipisahkan dari inti aplikasi.

## 💡 Konsep
Dalam arsitektur sebuah *Monorepo*, memisahkan kode yang sering digunakan ke dalam folder `packages/` adalah praktik terbaik untuk menjaga prinsip **DRY (Don't Repeat Yourself)** dan menjaga aplikasi utama (`apps/api` atau `apps/website`) tidak membengkak (tetap ramping).

## 📦 Apa yang Biasanya Ditaruh di Sini?
- **Shared UI Components**: Komponen *frontend* (misal: Button, Card, Table) jika suatu saat kita menggunakan lebih dari satu aplikasi frontend yang membagi *styling* sama.
- **Formatters / Utils Helpers**: Fungsi khusus manipulasi tanggal (format Indonesia), *converter* mata uang, dsb.
- **Custom Laravel Packages**: Modul *backend* kompleks yang bersifat independen (misal: *payment gateway integration*), sehingga bisa di-*link* via *composer path repository* ke aplikasi API utama.

## 🚀 Panduan Pembuatan Package
1. **Independen**: *Package* tidak boleh memiliki *hard-dependency* (bergantung penuh) ke file di folder `apps/`. Ia harus bisa berdiri sendiri layaknya *library open source*.
2. **Single Responsibility**: Setiap *package* idealnya hanya fokus melakukan satu tugas yang jelas.
3. **Standarisasi**: Penulisan kodenya wajib mematuhi standar *Clean Code* dan spasi yang tertulis di **[CONTRIBUTING.md](../../CONTRIBUTING.md)**.
