# Spike: Replacement for `laminas-crypt` (Security-Related)

## Overview

The Laminas team have officially abandoned the laminas-crypt package, which we previously relied on for symmetric encryption (specifically in the cache-encryption layer within olcs-transfer).
This spike investigated viable replacements and implemented a new encryption service using PHP’s built-in Sodium extension (libsodium), a modern, audited cryptographic library.

---

## Investigation Findings

### 1. Deprecation background

- `laminas-crypt` was deprecated and archived by Laminas in November 2023.\
  There will be **no further maintenance or security updates**.

### 2. Replacement candidates reviewed

| Candidate                          | Outcome       | Reason                                                                                                                      |
| ---------------------------------- | ------------- | --------------------------------------------------------------------------------------------------------------------------- |
| **OpenSSL (native PHP functions)** | ❌ Rejected   | API is low-level, error-prone, and lacks nonce authentication and AEAD support.                                             |
| **Defuse/php-encryption**          | ⚠️ Considered | Secure, but adds another dependency and abstraction we don’t need.                                                          |
| ``** (PHP Sodium extension)**      | ✅ Selected   | Actively maintained, bundled since PHP 7.2+, provides modern authenticated encryption (AEAD) via `XChaCha20-Poly1305-IETF`. |

### 3. Compatibility

- Our target PHP version is **8.2**, which includes `ext-sodium` by default.
- No external composer package required; only the extension must be enabled (`extension=sodium`).

---

## Implementation Summary

### 1. Introduced new service: `SodiumEncryptor`

A lightweight final class wrapping the native Sodium API for clarity and testability:

```php
final class SodiumEncryptor implements EncryptorInterface
{
    private const KEY_LEN   = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES; // 32 bytes
    private const NONCE_LEN = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES; // 24 bytes

    public function encrypt(string $key, string $plaintext, ?string $aad = null): string;
    public function decrypt(string $key, string $ciphertext, ?string $aad = null): string;
}
```

- Uses **XChaCha20-Poly1305-IETF AEAD encryption**.
- Automatically generates a new random nonce per encryption.
- Keys are validated to ensure **32 bytes (raw or Base64)**.
- Authentication prevents ciphertext tampering.

### 2. Updated dependent classes

- Replaced all `Laminas\Crypt\BlockCipher` usage in:
    - `CacheEncryption.php`
    - `CacheEncryptionFactory.php`
- Updated these components to inject `SodiumEncryptor` via the service manager.
- Removed any calls to `setKey()`, as keys are now passed directly into each encrypt/decrypt operation.

### 3. Configuration and secrets

- Two new Base64-encoded **32-byte keys** are generated and stored in our secrets manager:
    - `OLCS_NODE_CRYPT_KEY`
    - `OLCS_SHARED_CRYPT_KEY`
- Old cache entries will be invalidated automatically during the next release (cache flush).

### 4. Updated unit tests

- All tests refactored to mock `SodiumEncryptor` instead of `BlockCipher`.
- Updated method signatures (`encrypt($key, $plaintext)`, `decrypt($key, $ciphertext)`).
- Added new integration test verifying real round-trip encryption/decryption.
- Confirmed all tests pass successfully under PHP 8.2.

---

## 🔬 Verification

| Area                            | Result                                                            |
| ------------------------------- | ----------------------------------------------------------------- |
| **Unit tests**                  | ✅ All tests pass (including new sodium round-trip).              |
| **Cache encryption/decryption** | ✅ Confirmed stable and deterministic for matching key.           |
| **Secrets loading**             | ✅ Keys correctly decoded from Base64 and validated for 32 bytes. |
| **Backward compatibility**      | ⚠️ Old cache entries will be invalidated, as expected.            |

---

## Security Considerations

- Nonces are securely generated via `random_bytes()`.
- Ciphertext includes both nonce and authentication tag.
- AEAD provides integrity verification (no separate MAC required).
- Keys are never persisted to disk or logs.
- Secrets are managed externally (AWS Secrets Manager / Vault).

---

## Example key generation (DevOps)

```bash
head -c 32 /dev/urandom | base64
# Example output:
# U6uQ9fOeA5x5eIfpAS6ZOKqDhrqJ1RMFZl7bJ4Bf2aU=
```

Validation check:

```bash
php -r '$key = base64_decode("U6uQ9fOeA5x5eIfpAS6ZOKqDhrqJ1RMFZl7bJ4Bf2aU=", true);
echo strlen($key) === 32 ? "OK\n" : "Invalid key length\n";'
```

---

## Outcome Summary

| Goal                                  | Achieved |
| ------------------------------------- | -------- |
| Replace deprecated Laminas Crypt      | ✅       |
| Use secure, modern encryption (AEAD)  | ✅       |
| Maintain test coverage and simplicity |          |
| Keep integration impact low           | ✅       |
