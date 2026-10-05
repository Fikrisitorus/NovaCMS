<?php

namespace App\Livewire;

use App\Mail\ContactMessage;
use App\Services\NovaApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Form kontak sebagai Livewire component.
 *
 * Dipakai pada halaman kontak; alamat penerima diambil dari block
 * "contact" pada halaman API bertema kontak.
 */
class ContactForm extends Component
{
    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|email|max:150')]
    public string $email = '';

    #[Validate('required|string|max:2000')]
    public string $message = '';

    /**
     * Simpan pesan kontak dan kirim email ke penerima dari API.
     */
    public function save(NovaApiClient $api): void
    {
        $validated = $this->validate();

        $page = $api->getPage('contact');

        $recipient = collect($page['blocks'] ?? [])
            ->firstWhere('type', 'contact')['data']['recipient_email'] ?? null;

        // Hanya kirim bila penerima terkonfigurasi; selain itu simpan
        // pesan ke log agar tidak hilang saat belum ada konfigurasi SMTP.
        if ($recipient) {
            Mail::to($recipient)->send(new ContactMessage($validated));
        } else {
            info('Pesan kontak tanpa penerima: ', $validated);
        }

        $this->reset(['name', 'email', 'message']);

        session()->flash('success', 'Pesan Anda berhasil dikirim. Terima kasih!');
    }

    public function render(): View
    {
        return view('livewire.contact-form');
    }
}
