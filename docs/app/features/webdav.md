---
sidebar_position: 20
title: Internal WebDAV (MS Word Editing)
---

# Internal WebDAV (MS Word Letter Edit Journey)

Caseworkers edit RTF documents in MS Word via WebDAV. The `INTERNAL_WEBDAV` feature toggle enables an integrated [sabre/dav](https://sabre.io/dav/) server inside the internal app, replacing the previous external Apache WebDAV server and its [dvsa/jwt-stdin-verifier](https://github.com/dvsa/jwt-stdin-verifier) Go binary JWT Authoriser.

## Why

The legacy setup required a separate Apache server with direct filesystem access to the document store. The integrated approach removes that infrastructure — document reads and writes are proxied through the existing CQRS pipeline, abstracting the backend storage (S3/Flysystem) behind the same API used by the rest of the application. This allows us to replace the document store (currently EFS fronted by WebDAV), with anything else without breaking the MS Word letter editing feature.

## How It Works

```
MS Word → nginx (/internal-dav/) → webdav.php → sabre/dav → CQRS → Backend API → FileStore [S3,EFS,anything]
```

1. **Controller** generates a signed JWT (RS256) containing the document ID and a session key (`jti`). The user's Cognito tokens are cached in Redis under that `jti`. A `ms-word:ofe|u|` protocol link opens the document in Word, but the URL is that of the IUweb Internal Application, not an external DAV server.

2. **webdav.php** verifies the JWT, retrieves cached Cognito tokens from Redis, injects them into the session, then hands off to sabre/dav.

3. **VirtualFile** translates WebDAV GET → `Download` query and PUT → `OverwriteContent` command. A `RedisLockBackend` handles WebDAV locking. Only the document creator can overwrite (`CanOverwriteDocumentWithId` validator).

4. **Token refresh**: Cognito tokens (1hr) expire before the JWT (6hrs). Controllers proactively refresh before link generation; `webdav.php` attempts refresh mid-session using the cached refresh token.

## Configuration

```php
'webdav' => [
    'private_key' => '%webdav_jwt_private_key%',  // AWS Secrets Manager
    'default_lifetime_seconds' => 21600,           // 6 hours
    'url_pattern' => '...',                        // Legacy (toggle OFF URL pattern)
    'internal_url_pattern' => '...',               // sabre/dav (toggle ON URL pattern)
],
```

WebDAV uses a dedicated Redis connection (`WebDavRedisFactory`) because the shared `default-cache` connection uses `SERIALIZER_IGBINARY`, which conflicts with the manual `serialize()`/`unserialize()` used for token caching and lock storage.

## Key Files

| Component | Path |
|---|---|
| Entry point | `app/internal/public/webdav.php` |
| Virtual file (CQRS proxy) | `app/internal/module/Olcs/src/Service/WebDav/VirtualFile.php` |
| JWT generation/verification | `app/internal/module/Olcs/src/Service/Helper/WebDavJsonWebTokenGenerationService.php` |
| Session caching trait | `app/internal/module/Olcs/src/Controller/Traits/WebDavSessionTrait.php` |
| Redis lock backend | `app/internal/module/Olcs/src/Service/WebDav/RedisLockBackend.php` |
| nginx routing | `infra/docker/internal/internal.conf` |
