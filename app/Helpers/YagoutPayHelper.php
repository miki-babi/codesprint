<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class YagoutPayHelper
{
    public static function encryptAES256CBC(string $plaintext, string $key, string $iv): ?string
    {
        $block = 16;
        $len = strlen($plaintext);
        $pad = $block - ($len % $block);
        if ($pad === 0) $pad = $block;
        $padded = $plaintext . str_repeat(chr($pad), $pad);

        $method = "AES-256-CBC";
        $options = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;

        $cipher = openssl_encrypt($padded, $method, $key, $options, $iv);
        if ($cipher === false) {
            Log::error('Encryption failed: ' . openssl_error_string());
            return null;
        }
        return base64_encode($cipher);
    }

    public static function generateSha256Hash(string $input): string
    {
        return hash('sha256', $input);
    }
}
