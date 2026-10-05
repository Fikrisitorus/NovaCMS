import './bootstrap';
import Alpine from 'alpinejs';

// Livewire 3 sudah mem-bundle Alpine-nya sendiri; kita cukup mendaftarkan
// instance yang sama agar plugin seperti @alpinejs/collapse tetap bekerja.
window.Alpine = Alpine;
Alpine.start();
