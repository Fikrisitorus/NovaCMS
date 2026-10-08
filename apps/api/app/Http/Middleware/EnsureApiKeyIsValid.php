<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureApiKeyIsValid — middleware untuk endpoint API publik NovaCMS.
 *
 * Melakukan tiga hal:
 *  1. Validasi kunci API dari header Authorization: Bearer <key>
 *     atau query ?api_key=. Bila tidak valid, kembalikan 401.
 *  2. Rate limiting per-kunci: 60 request/menit. Header X-RateLimit-*
 *     di-set agar frontend bisa menampilkan throttle UI. Bila
 *     terlampaui, kembalikan 429.
 *  3. Catat waktu pemakaian terakhir (last_used_at) — ditulis ke cache
 *     dan di-flush oleh scheduler agar tidak membebani DB tiap request.
 */
class EnsureApiKeyIsValid
{
    /**
     * Batas request per menit per kunci API.
     */
    private const RATE_LIMIT = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->extractKey($request);

        if (blank($key)) {
            return $this->unauthorizedResponse();
        }

        $apiKey = ApiKey::findActive($key);

        if (! $apiKey) {
            return $this->unauthorizedResponse();
        }

        // Strict multi-tenant: kunci hanya berlaku jika website-nya masih
        // aktif. Pemilik yang menonaktifkan situsnya sekaligus memutus
        // semua akses API tanpa harus mencabut kunci satu per satu.
        if (! $apiKey->website->is_active) {
            return $this->forbiddenResponse();
        }

        // Sematkan kunci ke request agar controller bisa membatasi query
        // ke website pemilik kunci (isolasi tenant).
        $request->attributes->set('apiKey', $apiKey);

        $this->recordUsage($apiKey);

        return $this->handleRateLimit($apiKey, $request, $next);
    }

    /**
     * Ambil kunci dari header Authorization (Bearer) atau query string.
     */
    private function extractKey(Request $request): ?string
    {
        $header = $request->header('Authorization');

        if (is_string($header) && str_starts_with($header, 'Bearer ')) {
            return substr($header, strlen('Bearer '));
        }

        return $request->input('api_key');
    }

    /**
     * Simpan waktu pemakaian terakhir ke cache (di-flush per jam oleh
     * scheduler ke kolom last_used_at).
     */
    private function recordUsage(ApiKey $apiKey): void
    {
        Cache::put("api_key.{$apiKey->id}.last_used", now(), now()->addHour());
    }

    /**
     * Terapkan rate limiting per kunci dan tambahkan header standar
     * X-RateLimit-Limit / X-RateLimit-Remaining pada response.
     */
    private function handleRateLimit(ApiKey $apiKey, Request $request, Closure $next): Response
    {
        $cacheKey = "api_rate_limit.{$apiKey->id}";
        $window = 60;
        $now = time();
        $resetAt = $now + $window;

        // Cache::add mengembalikan false bila key sudah ada, sehingga
        // hit pertama dalam jendela waktu ini tidak di-increment ganda.
        $count = Cache::add($cacheKey, 1, $window) ? 1 : Cache::increment($cacheKey);

        $remaining = max(0, self::RATE_LIMIT - $count);

        if ($remaining === 0) {
            $retryAfter = $resetAt - $now;

            return response()->json([
                'message' => 'Terlalu banyak request. Coba lagi nanti.',
                'retry_after' => $retryAfter,
            ], 429, [
                'Retry-After' => (string) $retryAfter,
                'X-RateLimit-Limit' => (string) self::RATE_LIMIT,
                'X-RateLimit-Remaining' => '0',
                'X-RateLimit-Reset' => (string) $resetAt,
            ]);
        }

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', (string) self::RATE_LIMIT);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);
        $response->headers->set('X-RateLimit-Reset', (string) $resetAt);

        return $response;
    }

    /**
     * Response 401 standar untuk kunci yang hilang/tidak valid.
     */
    private function unauthorizedResponse(): Response
    {
        return response()->json([
            'message' => 'Kunci API tidak valid atau tidak disertakan.',
        ], 401);
    }

    /**
     * Response 403 untuk kunci yang website-nya sudah dinonaktifkan.
     */
    private function forbiddenResponse(): Response
    {
        return response()->json([
            'message' => 'Website pemilik kunci ini sudah dinonaktifkan.',
        ], 403);
    }
}
