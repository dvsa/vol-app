<?php

namespace OlcsTest\Service\Helper;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationService;

/**
 * @covers \Olcs\Service\Helper\WebDavJsonWebTokenGenerationService
 */
class WebDavJsonWebTokenGenerationServiceTest extends MockeryTestCase
{
    public const JWT_PRIVATE_KEY_TEMP_PATH = 'webdav_jwt_generation_service_private_key.pem';

    protected const JWT_PRIVATE_KEY_BASE64 = 'LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQpNSUlCT3dJQkFBSkJBSnRybTk2M3BYNlJIdG1oWTdGSndlTUcrWDU4bWMwbzRjUTlOMmp3SmVLWFlnM2h3bEpWCkVkTTByM1d6Y0FVcVhHeStvNlpWVGF5N3NnRmdTM1kvbVZVQ0F3RUFBUUpCQUkwdkxjTWVOTHBLL2lsWTBJVW0KcVhpZ3gxZzl2RUdBbDhaNmpiRklKa0kxTU45bEZmRVNMSHJWQTNKck1KZEh2R3RIN2ZoSHNoaUM1LzR1SDVpbAorU2tDSVFEa2dBYjJveThNMkUwQ05FbEJpbWhwTzN4MWV5bTNxNStPR0NZeEZHckxWd0loQUs0Z0IvMytodlpICk5SNm1rUldONktCRC95ZDMzaDFJa0djNmFXTTBhRUV6QWlFQWxQdE1qdjZ5cktOVEFuN296SXpicXRFWVF0ajgKeUQ1a0Y1ZHpQMGphb0owQ0lENWFJZ0tHSG5ZYVVaOUVMamYxdFJPT3hkT3dUTTFYcXI0TVlLaXhuNU9aQWlCOApaQkNaTG41dTRuWEFpZW1ENHA3bkF5dWp4azlQcWdBQmxUbXBJRHE1clE9PQotLS0tLUVORCBSU0EgUFJJVkFURSBLRVktLS0tLQ==';
    protected const JWT_PUBLIC_KEY_BASE64 = 'LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUZ3d0RRWUpLb1pJaHZjTkFRRUJCUUFEU3dBd1NBSkJBSnRybTk2M3BYNlJIdG1oWTdGSndlTUcrWDU4bWMwbwo0Y1E5TjJqd0plS1hZZzNod2xKVkVkTTByM1d6Y0FVcVhHeStvNlpWVGF5N3NnRmdTM1kvbVZVQ0F3RUFBUT09Ci0tLS0tRU5EIFBVQkxJQyBLRVktLS0tLQ==';
    protected const JWT_DEFAULT_LIFETIME_1_MINUTE = 60;
    protected const WEBDAV_URI_PATTERN = 'ms-word:ofe|u|https://testhost/documents-dav/%s/olcs/%s';

    protected const JWT_SUBJECT = 'user4574';
    protected const JWT_DOCUMENT = '0000000000000_OC00000000X_CoverLetter.rtf';

    protected WebDavJsonWebTokenGenerationService $sut;

    /**
     * @test
     * @dataProvider dataProviderDefaultLifetimeSecondsNotGreaterThanZero
     */
    public function constructWithDefaultLifetimeSecondsNotGreaterThanZeroThrowsException(int $defaultLifetimeSeconds): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(0x11);
        $this->expectExceptionMessage('default_lifetime_seconds: must be integer greater than zero');

        $this->sut = new WebDavJsonWebTokenGenerationService(
            static::JWT_PRIVATE_KEY_BASE64,
            $defaultLifetimeSeconds,
            self::WEBDAV_URI_PATTERN
        );
    }

    public function dataProviderDefaultLifetimeSecondsNotGreaterThanZero(): array
    {
        return [
            'Zero (int)' => [0],
            'Negative Integer (int)' => [-1],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderWithInvalidPrivateKeyInvalidVariants
     */
    public function constructWithInvalidPrivateKeyThrowsException(string $privateKey): void
    {
        if (!extension_loaded('openssl')) {
            $this->markAsRisky();
            $this->markTestSkipped('OpenSSL extension is not loaded. Test was skipped!');
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(0x22);
        $this->expectExceptionMessage('private_key: the path/key is not a valid PEM encoded private key');

        $this->sut = new WebDavJsonWebTokenGenerationService(
            $privateKey,
            static::JWT_DEFAULT_LIFETIME_1_MINUTE,
            self::WEBDAV_URI_PATTERN
        );
    }

    public function dataProviderWithInvalidPrivateKeyInvalidVariants(): array
    {
        return [
            'Base64 Encoded Positive Integer (int > string)' => [base64_encode(1)],
            'Base64 Encoded Negative Integer (int > string)' => [base64_encode(-1)],
            'FooBar (string)' => ['FooBar'],
            'Base64 Encoded Public Key (string)' => [static::JWT_PUBLIC_KEY_BASE64],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderWithInvalidPrivateKeyBase64OrPathInvalidVariants
     */
    public function constructWithInvalidPrivateKeyInvalidPathOrBase64ThrowsException(string $privateKey): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(0x21);
        $this->expectExceptionMessage('private_key: the value is not a valid path to or base64 encoded private key');

        $this->sut = new WebDavJsonWebTokenGenerationService(
            $privateKey,
            static::JWT_DEFAULT_LIFETIME_1_MINUTE,
            self::WEBDAV_URI_PATTERN
        );
    }

    public function dataProviderWithInvalidPrivateKeyBase64OrPathInvalidVariants(): array
    {
        return [
            'Base64 Encoded Zero (int > string)' => [base64_encode(0)],
            'Invalid Base64 characters (string)' => ['4rdHFh%2BHYoS8oLdVvbUzEVqB8Lvm7kSPnuwF0AAABYQ%3D'],
            'Non-Existent Path (string)' => ['/tmp/some-non-existent-path/to-some-file.pem'],
        ];
    }

    /**
     * @test
     */
    public function generateTokenIsCallable(): void
    {
        $this->sut = new WebDavJsonWebTokenGenerationService(
            static::JWT_PRIVATE_KEY_BASE64,
            static::JWT_DEFAULT_LIFETIME_1_MINUTE,
            self::WEBDAV_URI_PATTERN
        );
        $this->assertIsCallable([$this->sut, 'generateToken']);
    }

    /**
     * @test
     * @depends generateTokenIsCallable
     */
    public function generateTokenWithPrivateKeyAsBase64ReturnsJsonWebToken(): void
    {
        $this->sut = new WebDavJsonWebTokenGenerationService(
            static::JWT_PRIVATE_KEY_BASE64,
            static::JWT_DEFAULT_LIFETIME_1_MINUTE,
            self::WEBDAV_URI_PATTERN
        );

        $result = $this->sut->generateToken(static::JWT_SUBJECT, static::JWT_DOCUMENT);
        $decodedJwt = JWT::decode($result, new Key(base64_decode(static::JWT_PUBLIC_KEY_BASE64, true), WebDavJsonWebTokenGenerationService::TOKEN_ALGORITHM));

        $this->assertObjectHasProperty(WebDavJsonWebTokenGenerationService::TOKEN_PAYLOAD_KEY_SUBJECT, $decodedJwt);
        $this->assertEquals(static::JWT_SUBJECT, $decodedJwt->sub);

        $this->assertObjectHasProperty(WebDavJsonWebTokenGenerationService::TOKEN_PAYLOAD_KEY_DOCUMENT, $decodedJwt);
        $this->assertEquals(static::JWT_DOCUMENT, $decodedJwt->doc);
    }

    /**
     * @test
     * @depends generateTokenIsCallable
     */
    public function generateTokenWithPrivateKeyAsFileReturnsJsonWebToken(): void
    {
        file_put_contents(static::JWT_PRIVATE_KEY_TEMP_PATH, base64_decode(static::JWT_PRIVATE_KEY_BASE64, true), LOCK_EX);

        $this->sut = new WebDavJsonWebTokenGenerationService(
            static::JWT_PRIVATE_KEY_TEMP_PATH,
            static::JWT_DEFAULT_LIFETIME_1_MINUTE,
            self::WEBDAV_URI_PATTERN
        );

        $result = $this->sut->generateToken(static::JWT_SUBJECT, static::JWT_DOCUMENT);
        $decodedJwt = JWT::decode($result, new Key(base64_decode(static::JWT_PUBLIC_KEY_BASE64, true), WebDavJsonWebTokenGenerationService::TOKEN_ALGORITHM));

        $this->assertObjectHasProperty(WebDavJsonWebTokenGenerationService::TOKEN_PAYLOAD_KEY_SUBJECT, $decodedJwt);
        $this->assertEquals(static::JWT_SUBJECT, $decodedJwt->sub);

        $this->assertObjectHasProperty(WebDavJsonWebTokenGenerationService::TOKEN_PAYLOAD_KEY_DOCUMENT, $decodedJwt);
        $this->assertEquals(static::JWT_DOCUMENT, $decodedJwt->doc);
    }

    /**
     * @test
     * @depends generateTokenIsCallable
     */
    public function generateTokenWithPrivateKeyAsBase64ReturnsJsonWebTokenWithIssuedAtAndExpiry(): void
    {
        $this->sut = new WebDavJsonWebTokenGenerationService(
            static::JWT_PRIVATE_KEY_BASE64,
            static::JWT_DEFAULT_LIFETIME_1_MINUTE,
            self::WEBDAV_URI_PATTERN
        );

        $result = $this->sut->generateToken(static::JWT_SUBJECT, static::JWT_DOCUMENT);
        $decodedJwt = JWT::decode($result, new Key(base64_decode(static::JWT_PUBLIC_KEY_BASE64, true), WebDavJsonWebTokenGenerationService::TOKEN_ALGORITHM));

        $this->assertObjectHasProperty(WebDavJsonWebTokenGenerationService::TOKEN_PAYLOAD_KEY_ISSUED_AT, $decodedJwt);
        $this->assertObjectHasProperty(WebDavJsonWebTokenGenerationService::TOKEN_PAYLOAD_KEY_EXPIRES_AT, $decodedJwt);
    }

    public function testGFetJwtWebDavLink(): void
    {
        $this->sut = new WebDavJsonWebTokenGenerationService(
            static::JWT_PRIVATE_KEY_BASE64,
            static::JWT_DEFAULT_LIFETIME_1_MINUTE,
            self::WEBDAV_URI_PATTERN
        );

        $result = $this->sut->getJwtWebDavLink('JWT', 'ID');

        $this->assertSame('ms-word:ofe|u|https://testhost/documents-dav/JWT/olcs/ID', $result);
    }

    protected function setUp(): void
    {
        unset($this->sut);

        register_shutdown_function(function () {
            if (file_exists(static::JWT_PRIVATE_KEY_TEMP_PATH)) {
                unlink(static::JWT_PRIVATE_KEY_TEMP_PATH);
            }
        });
    }
}
