<?php

namespace App\Http\Controllers;

use App\Services\NovaApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function __construct(private readonly NovaApiClient $api) {}

    /**
     * Tampilkan halaman kontak. Menggunakan block "contact" pada halaman
     * bertema kontak bila tersedia, jika tidak pakai nilai default.
     */
    public function index(): View
    {
        $page = $this->api->getPage('contact');

        $block = collect($page['blocks'] ?? [])
            ->firstWhere('type', 'contact')['data'] ?? null;

        return view('contact.index', [
            'title' => $page['title'] ?? 'Kontak',
            'description' => $block['description'] ?? null,
            'recipient' => $block['recipient_email'] ?? config('mail.from.address'),
        ]);
    }

    /**
     * Validasi pesan kontak lalu kirim email ke alamat penerima dari
     * block "contact". Mengembalikan pesan sukses atau error validasi.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ])->validate();

        $page = $this->api->getPage('contact');

        $recipient = collect($page['blocks'] ?? [])
            ->firstWhere('type', 'contact')['data']['recipient_email'] ?? null;

        // Hanya kirim bila penerima terkonfigurasi; selain itu simpan
        // pesan ke log agar tidak hilang saat belum ada konfigurasi SMTP.
        if ($recipient) {
            Mail::to($recipient)->send(new ContactMessage($validated));
        } else {
            info('Pesan kontak tanpa penerima: ', $validated);
        }

        return back()->with('success', 'Pesan Anda berhasil dikirim. Terima kasih!');
    }
}
