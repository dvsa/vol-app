<?php

namespace Olcs\Controller\Traits;

use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Laminas\Session\Container;

trait WebDavSessionTrait
{
    public const REDIS_WEBDAV_AUTH_PREFIX = 'webdav_auth:';

    private function isInternalWebDavEnabled(): bool
    {
        $result = $this->handleQuery(
            IsEnabledQry::create(['ids' => [FeatureToggle::INTERNAL_WEBDAV]])
        );

        return $result->getResult()['isEnabled'] ?? false;
    }

    private function refreshCognitoTokenForWebDav(): void
    {
        $identityContainer = new Container('Identity');
        $storage = $identityContainer->offsetGet('storage');

        if (!is_array($storage)) {
            return;
        }

        $tokenExpires = $storage['Token']['expires'] ?? 0;
        $webDavLifetime = $this->webDavJsonWebTokenGenerationService->getDefaultLifetimeSeconds();

        if ($tokenExpires > 0 && ($tokenExpires - time()) < $webDavLifetime) {
            try {
                $username = $storage['AccessTokenClaims']['username'] ?? null;
                if ($username && !empty($storage['Token']['refresh_token'])) {
                    $newIdentity = $this->refreshTokenService->refreshTokens(
                        $storage['Token'],
                        $username
                    );
                    $identityContainer->offsetSet('storage', $newIdentity);
                }
            } catch (\Throwable) {
                // Continue with existing tokens — they may still be valid
            }
        }
    }

    private function cacheSessionTokens(string $jti, int $ttl): void
    {
        if ($this->redis === null) {
            return;
        }

        $identityContainer = new Container('Identity');
        $storage = $identityContainer->offsetGet('storage');

        if (!is_array($storage)) {
            return;
        }

        $tokenExpires = $storage['Token']['expires'] ?? 0;
        if ($tokenExpires > 0) {
            $remaining = $tokenExpires - time();
            if ($remaining <= 0) {
                return;
            }
            $ttl = min($ttl, $remaining);
        }

        $tokenData = [
            'AccessToken' => $storage['AccessToken'] ?? null,
            'RefreshToken' => $storage['RefreshToken'] ?? null,
            'Token' => $storage['Token'] ?? null,
            'AccessTokenClaims' => $storage['AccessTokenClaims'] ?? null,
        ];

        $key = self::REDIS_WEBDAV_AUTH_PREFIX . $jti;
        $this->redis->setex($key, $ttl, serialize($tokenData));
    }

    private function generateWebDavUrl(string $identifier, int $documentId, bool $internalWebDav, ?int $documentSize = null): string
    {
        if ($internalWebDav) {
            $jti = bin2hex(random_bytes(16));
            $extension = pathinfo($identifier, PATHINFO_EXTENSION) ?: 'rtf';
            $jwt = $this->webDavJsonWebTokenGenerationService->generateToken(
                'intusr',
                $extension,
                null,
                $jti,
                $documentId,
                $documentSize,
            );
            $this->cacheSessionTokens(
                $jti,
                $this->webDavJsonWebTokenGenerationService->getDefaultLifetimeSeconds()
            );
            return $this->webDavJsonWebTokenGenerationService->getInternalWebDavLink($jwt, $documentId . '.' . $extension);
        }

        $jwt = $this->webDavJsonWebTokenGenerationService->generateToken(
            'intusr',
            $identifier,
        );
        return $this->webDavJsonWebTokenGenerationService->getJwtWebDavLink($jwt, $identifier);
    }
}
