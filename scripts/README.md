# Scripts Directory

Folder ini dikhususkan untuk menyimpan sekumpulan *script automation* dan alat utilitas (Utility Tools) yang membantu meringankan proses *Development*, *Testing*, maupun *Deployment*.

## 📂 Jenis Script yang Ada
- **Bash Scripts (`.sh`)**: Biasanya dipakai untuk otomatisasi di OS Linux/Mac (contoh: *script deploy* otomatis ke VPS, *script build* massal, eksekusi migrasi & *seed database* cepat).
- **Node Scripts (`.js`)**: Dipakai jika butuh *scripting logic* yang sedikit kompleks, misal memanipulasi file JSON antar folder.
- **PowerShell (`.ps1`)**: Alternatif untuk mengeksekusi otomatisasi bagi *developer* yang memakai OS Windows secara native.

## ⚠️ Keamanan dan Privasi (PENTING)
- **DILARANG KERAS** menyimpan kredensial asli (*API Key, Token, Password Database*) dalam teks terang di dalam *script* mana pun!
- Semua *script* yang memerlukan autentikasi harus membaca nilai (variabel) dari file `.env`.
