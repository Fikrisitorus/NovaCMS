# Design Directory

Folder ini difungsikan sebagai tempat penyimpanan arsip desain antarmuka (UI/UX) dan *assets* visual untuk proyek NovaCMS.

## 📂 Struktur Folder
- **`/figma`**: Berisi tautan ekspor desain akhir atau dokumen *link* Figma resmi yang dikerjakan oleh tim desainer UI/UX.
- **`/wireframes`**: Berisi sketsa kasar awal (*lo-fi/wireframes*) terkait alur sistem sebelum diubah menjadi desain indah (*hi-fi*).
- **`/assets`**: File mentah (logo `.svg` asli, maskot, ilustrasi, font *custom*) yang belum terkompresi. 
  
*(Catatan: Jangan menaruh aset *production* yang akan di-load oleh browser langsung di sini, melainkan tempatkan di folder `public/` milik masing-masing aplikasi di dalam folder `apps/`)*.

## 📌 Ketentuan Developer
Semua Frontend Developer harus selalu merujuk pada desain terbaru yang tercatat di dokumen ini untuk memastikan akurasi implementasi visual UI (*Pixel Perfect Design*). Acuan penulisan dan tata krama kodenya tetap mengikuti **[CONTRIBUTING.md](../../CONTRIBUTING.md)**.
