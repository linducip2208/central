<?php

namespace App\Services;

/**
 * TOTP RFC 6238 murni (tanpa dependensi) untuk 2FA opsional.
 */
class TotpService
{
    public function __construct(protected int $step = 30, protected int $digits = 6) {}

    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    public function provisioningUrl(string $email, string $secret, string $issuer = 'MBG Kitchen'): string
    {
        return 'otpauth://totp/'.rawurlencode($issuer.':'.$email).'?secret='.$secret.'&issuer='.rawurlencode($issuer).'&digits='.$this->digits.'&period='.$this->step;
    }

    public function verify(string $secret, string $code, int $window = 1, ?int $at = null): bool
    {
        $at ??= time();
        $code = trim($code);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->code($secret, $at + $i * $this->step), $code)) {
                return true;
            }
        }

        return false;
    }

    public function code(string $secret, ?int $at = null): string
    {
        $at ??= time();
        $counter = (int) floor($at / $this->step);
        $key = $this->base32Decode($secret);
        $msg = pack('N*', 0, $counter);
        $hash = hash_hmac('sha1', $msg, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $value = ((ord($hash[$offset]) & 0x7F) << 24) | (ord($hash[$offset + 1]) << 16) | (ord($hash[$offset + 2]) << 8) | ord($hash[$offset + 3]);

        return str_pad((string) ($value % 10 ** $this->digits), $this->digits, '0', STR_PAD_LEFT);
    }

    protected function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($data) as $c) {
            $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0');
            $out .= $alphabet[bindec($chunk)];
        }

        return $out;
    }

    protected function base32Decode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $data = strtoupper(rtrim($data, '='));
        $bits = '';
        foreach (str_split($data) as $c) {
            $pos = strpos($alphabet, $c);
            if ($pos === false) {
                throw new \InvalidArgumentException('Secret base32 tidak valid.');
            }
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) < 8) {
                break;
            }
            $out .= chr(bindec($byte));
        }

        return $out;
    }
}
