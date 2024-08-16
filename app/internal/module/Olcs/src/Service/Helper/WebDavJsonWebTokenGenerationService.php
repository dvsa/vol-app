<?php

namespace Olcs\Service\Helper;

use Firebase\JWT\JWT;

class WebDavJsonWebTokenGenerationService
{
    public const TOKEN_ALGORITHM = 'RS256';

    public const TOKEN_PAYLOAD_KEY_SUBJECT = 'sub';
    public const TOKEN_PAYLOAD_KEY_DOCUMENT = 'doc';
    public const TOKEN_PAYLOAD_KEY_ISSUED_AT = 'iat';
    public const TOKEN_PAYLOAD_KEY_EXPIRES_AT = 'exp';


    private int $defaultLifetimeSeconds;
    private string $privateKey;

    public function __construct(string $privateKey, int $defaultLifetimeSeconds, private string $urlPattern)
    {
        $this->defaultLifetimeSeconds = $this->parseDefaultLifetimeSeconds($defaultLifetimeSeconds);
        $this->privateKey = $this->parsePrivateKey($privateKey);
    }

    public function generateToken(string $subject, string $document, int $lifetimeSeconds = null): string
    {
        $payload = [
            static::TOKEN_PAYLOAD_KEY_SUBJECT => $subject,
            static::TOKEN_PAYLOAD_KEY_DOCUMENT => $document,
            static::TOKEN_PAYLOAD_KEY_ISSUED_AT => time(),
            static::TOKEN_PAYLOAD_KEY_EXPIRES_AT => time() + ($lifetimeSeconds ?? $this->defaultLifetimeSeconds),
        ];

        return JWT::encode($payload, $this->privateKey, static::TOKEN_ALGORITHM);
    }

    protected function parseDefaultLifetimeSeconds(int $defaultLifetimeSeconds): int
    {
        if ($defaultLifetimeSeconds < 1) {
            throw new \InvalidArgumentException('default_lifetime_seconds: must be integer greater than zero', 0x11);
        }

        return $defaultLifetimeSeconds;
    }

    protected function parsePrivateKey(string $privateKey): string
    {
        $privateKey = @file_exists($privateKey) ? file_get_contents($privateKey) : base64_decode($privateKey, true);

        if (!$privateKey) {
            throw new \InvalidArgumentException('private_key: the value is not a valid path to or base64 encoded private key', 0x21);
        }

        if (extension_loaded('openssl') && !openssl_pkey_get_private($privateKey)) {
            throw new \InvalidArgumentException('private_key: the path/key is not a valid PEM encoded private key', 0x22);
        }

        return $privateKey;
    }

    /**
     * @param $jwt string JWT token
     * @param $identifier string Document Identifier string from db
     * @return string
     */
    public function getJwtWebDavLink($jwt, $identifier)
    {
        return sprintf($this->urlPattern, $jwt, $identifier);
    }
}
