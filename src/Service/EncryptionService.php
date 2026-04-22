<?php

namespace App\Service;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class EncryptionService
{
    private string $encryptionKey;
    private PasswordHasherFactoryInterface $passwordHasherFactory;

    public function __construct(string $encryptionKey, PasswordHasherFactoryInterface $passwordHasherFactory)
    {
        $this->encryptionKey = $encryptionKey;
        $this->passwordHasherFactory = $passwordHasherFactory;
    }

    /**
     * Chiffre une donnée (UID RFID) avec AES-256
     */
    public function encrypt(string $data): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Déchiffre une donnée (UID RFID)
     */
    public function decrypt(string $encryptedData): string
    {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
        return openssl_decrypt($encrypted, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
    }

    /**
     * Hache un code PIN avec bcrypt
     */
    public function hashPin(string $pin): string
    {
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher('App\Entity\Utilisateur');
        return $passwordHasher->hash($pin);
    }

    /**
     * Vérifie si un code PIN correspond à son hash
     */
    public function verifyPin(string $pin, string $hashedPin): bool
    {
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher('App\Entity\Utilisateur');
        return $passwordHasher->verify($hashedPin, $pin);
    }
}