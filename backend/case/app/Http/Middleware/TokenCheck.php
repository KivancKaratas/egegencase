<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TokenCheck
{
    // Doğru token değeri
    protected $token = '2BH52wAHrAymR7wP3CASt';
    
    // IP engelleme süresi (saniye cinsinden, 600 saniye = 10 dakika)
    protected $blockDuration = 600;

    /**
     * Middleware işlemi: Bearer token kontrolü ve IP engelleme mekanizması
     * 
     * @param \Illuminate\Http\Request $request Gelen HTTP isteği
     * @param \Closure $next İstek geçişi için bir sonraki middleware
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Kullanıcının IP adresini al
        $ip = $request->ip();

        // IP için daha önce yapılmış başarısız giriş denemelerini al (Varsayılan 0)
        $failedAttempts = Cache::get("failed_attempts_{$ip}", 0);

        // Eğer IP şu anda engelliyse, 403 hata döndür
        if (Cache::has("blocked_{$ip}")) {
            return response()->json([
                'message' => 'IP adresiniz engellenmiştir. Lütfen daha sonra tekrar deneyin.'
            ], 403);
        }

        // Gelen istekteki Bearer Token ile beklenen tokenı karşılaştır
        if ($request->bearerToken() !== $this->token) {
            // Eğer token hatalıysa başarısız deneme sayısını artır
            $failedAttempts++;

            // Başarısız deneme sayısını cache'e kaydet
            Cache::put("failed_attempts_{$ip}", $failedAttempts, $this->blockDuration);

            // Eğer başarısız denemeler 10'dan fazlaysa IP'yi engelle ve 403 hata döndür
            if ($failedAttempts >= 10) {
                Cache::put("blocked_{$ip}", true, $this->blockDuration);
                return response()->json([
                    'message' => 'Birden fazla başarısız giriş denemesi nedeniyle IP adresiniz engellendi.'
                ], 403);
            }

            // Token hatalıysa ve henüz engellenmediyse 401 yetkisiz hatası döndür
            return response()->json([
                'message' => 'Yetkisiz erişim. Geçersiz token.'
            ], 401);
        }

        // Eğer token geçerliyse bir sonraki middleware'e geç
        return $next($request);
    }
}
