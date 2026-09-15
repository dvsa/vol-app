<?php

namespace Olcs\Controller\Traits;

use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Laminas\Session\Container;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationService;

trait WebDavSessionTrait
{
    public const REDIS_WEBDAV_AUTH_PREFIX = 'webdav_auth:';

    /**
     * Proactively refresh the Cognito access token only when it is within this many seconds of
     * expiry, rather than on every link render. Longer editing sessions are kept alive mid-flight
     * by webdav.php using the cached refresh token.
     */
    private const COGNITO_REFRESH_THRESHOLD_SECONDS = 300;

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

        if ($tokenExpires > 0 && ($tokenExpires - time()) < self::COGNITO_REFRESH_THRESHOLD_SECONDS) {
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

        $tokenData = [
            'AccessToken' => $storage['AccessToken'] ?? null,
            'RefreshToken' => $storage['RefreshToken'] ?? null,
            'Token' => $storage['Token'] ?? null,
            'AccessTokenClaims' => $storage['AccessTokenClaims'] ?? null,
        ];

        // Cache for the full WebDAV link lifetime, not the (shorter) Cognito access-token life: the
        // cached refresh token lets webdav.php renew the access token mid-session, so the blob must
        // outlive the access token, otherwise editing sessions longer than ~1h fail on save with
        // "Session tokens not found or expired".
        $key = self::REDIS_WEBDAV_AUTH_PREFIX . $jti;
        $this->redis->setex($key, $ttl, serialize($tokenData));
    }

    private function generateWebDavUrl(string $identifier, int $documentId, bool $internalWebDav, ?int $documentSize = null): string
    {
        if ($internalWebDav) {
            $jti = bin2hex(random_bytes(16));
            $extension = $this->normaliseWebDavExtension($identifier);
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

    /**
     * Normalise a document extension to the lowercase, allow-listed form used by webdav.php, so the
     * generated link and the sabre/dav virtual node name agree exactly (they are matched with ===).
     */
    private function normaliseWebDavExtension(string $identifier): string
    {
        $extension = strtolower(pathinfo($identifier, PATHINFO_EXTENSION));

        return in_array($extension, WebDavJsonWebTokenGenerationService::ALLOWED_DOCUMENT_EXTENSIONS, true)
            ? $extension
            : WebDavJsonWebTokenGenerationService::DEFAULT_DOCUMENT_EXTENSION;
    }
}
