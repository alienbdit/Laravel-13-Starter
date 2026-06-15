<?php

namespace App\Encryption;

use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Encryption\Encrypter as BaseEncrypter;

/**
 * PHP 8.5 workaround: openssl_encrypt() with a $tag by-reference argument triggers
 * an internal ArgumentCountError against openssl_get_md_methods() for non-AEAD ciphers.
 * Fix: only pass $tag when the cipher actually uses AEAD (GCM); omit it for CBC.
 */
class Encrypter extends BaseEncrypter
{
    private const AEAD_CIPHERS = ['aes-128-gcm', 'aes-256-gcm'];

    public function encrypt($value, $serialize = true): string
    {
        $cipher = strtolower($this->cipher);
        $isAead = in_array($cipher, self::AEAD_CIPHERS, true);

        $iv = random_bytes(openssl_cipher_iv_length($cipher));

        if ($isAead) {
            $encrypted = \openssl_encrypt(
                $serialize ? serialize($value) : $value,
                $cipher, $this->key, 0, $iv, $tag
            );
        } else {
            $encrypted = \openssl_encrypt(
                $serialize ? serialize($value) : $value,
                $cipher, $this->key, 0, $iv
            );
            $tag = null;
        }

        if ($encrypted === false) {
            throw new EncryptException('Could not encrypt the data.');
        }

        $iv  = base64_encode($iv);
        $tag = base64_encode($tag ?? '');
        $mac = $isAead ? '' : $this->hash($iv, $encrypted, $this->key);

        $json = json_encode(
            ['iv' => $iv, 'value' => $encrypted, 'mac' => $mac, 'tag' => $tag],
            JSON_UNESCAPED_SLASHES
        );

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptException('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    public function decrypt($payload, $unserialize = true): mixed
    {
        $payload = $this->getJsonPayload($payload);
        $cipher  = strtolower($this->cipher);
        $isAead  = in_array($cipher, self::AEAD_CIPHERS, true);

        $iv  = base64_decode($payload['iv']);
        $tag = empty($payload['tag']) ? null : base64_decode($payload['tag']);

        $this->ensureTagIsValid($tag);

        $keys     = $this->getAllKeys();
        $validKey = null;

        foreach ($keys as $key) {
            if ($this->shouldValidateMac()) {
                if ($this->validMacForKey($payload, $key) && $validKey === null) {
                    $validKey = $key;
                }
                continue;
            }

            $decrypted = $isAead
                ? \openssl_decrypt($payload['value'], $cipher, $key, 0, $iv, $tag ?? '')
                : \openssl_decrypt($payload['value'], $cipher, $key, 0, $iv);

            if ($decrypted !== false) {
                break;
            }
        }

        if ($this->shouldValidateMac() && $validKey === null) {
            throw new \Illuminate\Contracts\Encryption\DecryptException('The MAC is invalid.');
        }

        if ($this->shouldValidateMac()) {
            $decrypted = $isAead
                ? \openssl_decrypt($payload['value'], $cipher, $validKey, 0, $iv, $tag ?? '')
                : \openssl_decrypt($payload['value'], $cipher, $validKey, 0, $iv);
        }

        if (($decrypted ?? false) === false) {
            throw new \Illuminate\Contracts\Encryption\DecryptException('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }
}
