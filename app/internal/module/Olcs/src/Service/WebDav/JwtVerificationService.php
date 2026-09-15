<?php

declare(strict_types=1);

namespace Olcs\Service\WebDav;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtVerificationService
{
    public function __construct(
        private readonly string $publicKey,
        private readonly string $algorithm = 'RS256',
    ) {
    }

    /**
     * Decode and verify a JWT token.
     *
     * @return object The decoded payload
     * @throws \Firebase\JWT\ExpiredException
     * @throws \Firebase\JWT\SignatureInvalidException
     * @throws \UnexpectedValueException
     */
    public function verify(string $token): object
    {
        return JWT::decode($token, new Key($this->publicKey, $this->algorithm));
    }
}
