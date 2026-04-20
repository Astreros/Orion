<?php

namespace App\Service;

use Firebase\JWT\JWT;

class TokenGeneratorService
{
    private string $encryptionKey;

    public function __construct(string $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * Génère un token JWT chiffré.
     */
    public function generateToken(array $data, int $lifetime): string
    {
        /**
         * Claim standard (champ standardisé) défini par la RFC 7519 pour les JWT
         */
        $payload = [
            'iat' => time(),
            'exp' => time() + $lifetime,
            'data' => $data,
        ];

        return JWT::encode($payload, $this->encryptionKey, 'HS256');
    }
}