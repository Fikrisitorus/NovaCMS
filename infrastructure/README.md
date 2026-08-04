# Docker Directory

Folder ini berisi konfigurasi pendukung *containerization* spesifik untuk proyek NovaCMS. File utamanya adalah `docker-compose.yml` di root, dan folder ini menyimpan konfigurasi spesifiknya.

## 📂 Konteks Folder
- **Database Image Setup**: Konfigurasi MySQL / PostgreSQL (termasuk *volume mapping* dan *initial script SQL*).
- **Service Pendukung**: File *Dockerfile* khusus untuk *services* tambahan (misal Nginx, Redis) jika default image dari *Docker Hub* butuh kustomisasi.

## 🚀 Panduan Local Environment
Dengan setup Docker ini, siapapun *developer* baru yang masuk bisa memiliki *database* dan *server* pendukung tanpa harus repot instalasi XAMPP/Laragon.
1. Cukup atur `.env` lokal.
2. Jalankan `docker-compose up -d`.
3. Semua sistem *(database, cache)* langsung siap terhubung ke `apps/api`.
