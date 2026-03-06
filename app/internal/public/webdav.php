<?php

// phpcs:ignoreFile

declare(strict_types=1);

/**
 * WebDAV front controller for MS Word document editing.
 *
 * This dedicated entry point handles WebDAV requests from MS Word.
 * It uses sabre/dav for protocol handling and proxies document
 * operations through the existing CQRS mechanism.
 */

error_reporting(E_ALL & ~E_USER_DEPRECATED);

chdir(dirname(__DIR__));

ini_set('intl.default_locale', 'en_GB');
date_default_timezone_set('Europe/London');

include __DIR__ . '/../vendor/autoload.php';

use Common\Auth\Service\RefreshTokenService;
use Laminas\Session\Container;
use Olcs\Service\WebDav\JwtVerificationService;
use Olcs\Service\WebDav\RedisLockBackend;
use Olcs\Service\WebDav\VirtualDirectory;
use Olcs\Service\WebDav\VirtualFile;
use Olcs\Service\WebDav\WebDavRedisFactory;
use Sabre\DAV;

// Bootstrap Laminas to get the service container
$container = require __DIR__ . '/../config/container.php';

// Parse JWT from URL: /internal-dav/{JWT}
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);

if (!preg_match('#^/internal-dav/([^/]+)(/.*)?$#', $path, $matches)) {
    http_response_code(400);
    echo 'Invalid WebDAV URL';
    exit;
}

$jwtToken = $matches[1];

$annotationBuilder = $container->get('TransferAnnotationBuilder');
$queryService = $container->get('QueryService');

// Verify JWT
try {
    $jwtService = $container->get(JwtVerificationService::class);
    $payload = $jwtService->verify($jwtToken);
} catch (\Firebase\JWT\ExpiredException) {
    http_response_code(401);
    header('WWW-Authenticate: Bearer error="invalid_token", error_description="Token expired"');
    echo 'JWT expired';
    exit;
} catch (\Throwable $e) {
    http_response_code(401);
    header('WWW-Authenticate: Bearer error="invalid_token"');
    echo 'Invalid JWT';
    exit;
}

// Extract claims
$jti = $payload->jti ?? null;
$documentId = $payload->did ?? null;
$documentPath = $payload->doc ?? null;
$documentSize = (int) ($payload->dsz ?? 0);

if ($jti === null || $documentId === null || $documentPath === null) {
    http_response_code(400);
    echo 'JWT missing required claims';
    exit;
}

// Retrieve cached Cognito tokens from Redis
$redis = $container->get(WebDavRedisFactory::SERVICE_NAME);
if ($redis === null) {
    http_response_code(503);
    echo 'Service temporarily unavailable';
    exit;
}

$redisKey = 'webdav_auth:' . $jti;
$cachedTokenData = $redis->get($redisKey);

if ($cachedTokenData === false) {
    http_response_code(401);
    echo 'Session tokens not found or expired';
    exit;
}

$tokenData = unserialize($cachedTokenData, ['allowed_classes' => false]);

if (!is_array($tokenData)) {
    http_response_code(401);
    echo 'Cached session data is corrupt or invalid';
    exit;
}

// Validate token data before proceeding
$accessToken = $tokenData['AccessToken'] ?? null;
if (empty($accessToken)) {
    http_response_code(401);
    echo 'No access token in cached session data';
    exit;
}

// Attempt token refresh if Cognito access token has expired
$tokenExpires = $tokenData['Token']['expires'] ?? 0;
if ($tokenExpires > 0 && $tokenExpires <= time()) {
    $refreshToken = $tokenData['Token']['refresh_token'] ?? null;
    $username = $tokenData['AccessTokenClaims']['username'] ?? null;

    if ($refreshToken && $username) {
        try {
            $refreshTokenService = $container->get(RefreshTokenService::class);
            $newIdentity = $refreshTokenService->refreshTokens($tokenData['Token'], $username);
            $tokenData = $newIdentity;

            // Update the cached tokens in Redis with the remaining JWT lifetime
            $jwtExpiry = $payload->exp ?? 0;
            $remainingTtl = max($jwtExpiry - time(), 1);
            $redis->setex($redisKey, $remainingTtl, serialize($tokenData));
        } catch (\Throwable) {
            http_response_code(401);
            echo 'Cognito access token has expired and refresh failed. Please re-generate the document link.';
            exit;
        }
    } else {
        http_response_code(401);
        echo 'Cognito access token has expired. Please re-generate the document link.';
        exit;
    }
}

// Inject tokens into Identity session container so CQRS calls work
$identityContainer = new Container('Identity');
$identityContainer->offsetSet('storage', $tokenData);
session_write_close();

// Build the virtual filesystem tree
// Use documentId + extension as filename (all operations use the ID via CQRS)
$extension = $documentPath ?: 'rtf';
$filename = $documentId . '.' . $extension;

$virtualFile = new VirtualFile(
    $filename,
    (int) $documentId,
    $annotationBuilder,
    $queryService,
    $container->get('CommandService'),
    $documentSize,
);

$rootDir = new VirtualDirectory('root', [$virtualFile]);

// Configure sabre/dav server
$server = new DAV\Server($rootDir);
$server->setBaseUri('/internal-dav/' . $jwtToken . '/');

// MS Office compatibility: hide LockRoot to prevent crashes
DAV\Xml\Property\LockDiscovery::$hideLockRoot = true;

// Add lock plugin with Redis backend
$lockBackend = new RedisLockBackend($redis);
$lockPlugin = new DAV\Locks\Plugin($lockBackend);
$server->addPlugin($lockPlugin);

// Execute the WebDAV request
$server->exec();
