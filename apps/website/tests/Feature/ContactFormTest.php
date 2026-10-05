<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Test form kontak: Livewire component dan halaman kontak.
 */
class ContactFormTest extends TestCase
{
    /**
     * Halaman kontak menampilkan form Livewire.
     */
    public function test_halaman_kontak_menampilkan_form(): void
    {
        Http::fake([
            '*/api/v1/pages/contact' => Http::response([
                'data' => [
                    'title' => 'Kontak Kami',
                    'slug' => 'contact',
                    'blocks' => [
                        ['type' => 'contact', 'data' => [
                            'recipient_email' => 'support@example.com',
                            'description' => 'Kirim pertanyaan Anda.',
                        ]],
                    ],
                ],
            ]),
        ]);

        $response = $this->get(route('contact.index'));

        $response->assertOk();
        $response->assertSee('Kirim pertanyaan Anda.');
    }

    /**
     * Livewire component mengirim email ke penerima dari block contact.
     */
    public function test_livewire_form_mengirim_email_ke_penerima_dari_api(): void
    {
        Mail::fake();
        Http::fake([
            '*/api/v1/pages/contact' => Http::response([
                'data' => [
                    'title' => 'Kontak',
                    'slug' => 'contact',
                    'blocks' => [
                        ['type' => 'contact', 'data' => ['recipient_email' => 'support@example.com']],
                    ],
                ],
            ]),
        ]);

        Livewire::test('contact-form')
            ->set('name', 'Budi')
            ->set('email', 'budi@example.com')
            ->set('message', 'Halo, saya butuh bantuan.')
            ->call('save')
            ->assertHasNoErrors();

        Mail::assertSent(ContactMessage::class, function ($mail) {
            return $mail->hasTo('support@example.com');
        });
    }

    /**
     * Validasi mencegah pengiriman data kosong.
     */
    public function test_livewire_form_memvalidasi_input_kosong(): void
    {
        Livewire::test('contact-form')
            ->call('save')
            ->assertHasErrors(['name', 'email', 'message']);
    }
}
