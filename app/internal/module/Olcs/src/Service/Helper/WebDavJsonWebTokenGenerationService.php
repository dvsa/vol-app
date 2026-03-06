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
    public const TOKEN_PAYLOAD_KEY_JWT_ID = 'jti';
    public const TOKEN_PAYLOAD_KEY_DOCUMENT_ID = 'did';
    public const TOKEN_PAYLOAD_KEY_DOCUMENT_SIZE = 'dsz';


    private readonly int $defaultLifetimeSeconds;
    private readonly string $privateKey;

    public function __construct(string $privateKey, int $defaultLifetimeSeconds, private readonly string $urlPattern, private readonly string $internalUrlPattern)
    {
        $this->defaultLifetimeSeconds = $this->parseDefaultLifetimeSeconds($defaultLifetimeSeconds);
        $this->privateKey = $this->parsePrivateKey($privateKey);
    }

    public function generateToken(string $subject, string $document, int $lifetimeSeconds = null, ?string $jti = null, ?int $documentId = null, ?int $documentSize = null): string
    {
        $payload = [
            static::TOKEN_PAYLOAD_KEY_SUBJECT => $subject,
            static::TOKEN_PAYLOAD_KEY_DOCUMENT => $document,
            static::TOKEN_PAYLOAD_KEY_ISSUED_AT => time(),
            static::TOKEN_PAYLOAD_KEY_EXPIRES_AT => time() + ($lifetimeSeconds ?? $this->defaultLifetimeSeconds),
        ];

        if ($jti !== null) {
            $payload[static::TOKEN_PAYLOAD_KEY_JWT_ID] = $jti;
        }

        if ($documentId !== null) {
            $payload[static::TOKEN_PAYLOAD_KEY_DOCUMENT_ID] = $documentId;
        }

        if ($documentSize !== null) {
            $payload[static::TOKEN_PAYLOAD_KEY_DOCUMENT_SIZE] = $documentSize;
        }

        return JWT::encode($payload, $this->privateKey, static::TOKEN_ALGORITHM);
    }

    public function getDefaultLifetimeSeconds(): int
    {
        return $this->defaultLifetimeSeconds;
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
     * Build a legacy WebDAV link (IIS-based, toggle OFF path).
     */
    public function getJwtWebDavLink(string $jwt, string $identifier): string
    {
        return sprintf($this->urlPattern, $jwt, $identifier);
    }

    /**
     * Build an internal WebDAV link (sabre/dav, toggle ON path).
     */
    public function getInternalWebDavLink(string $jwt, string $filename): string
    {
        return sprintf($this->internalUrlPattern, $jwt, $filename);
    }
}
