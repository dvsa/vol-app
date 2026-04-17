<?php

declare(strict_types=1);

namespace OlcsTest\Service\Helper;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationService;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Service\Helper\WebDavJsonWebTokenGenerationService::class)]
class WebDavJsonWebTokenGenerationServiceTest extends MockeryTestCase
{
    public const JWT_PRIVATE_KEY_TEMP_PATH = 'webdav_jwt_generation_service_private_key.pem';

    // 2048-bit RSA keys for testing (php-jwt v7 requires minimum 2048-bit)
    protected const JWT_PRIVATE_KEY_BASE64 = 'LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV2UUlCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktjd2dnU2pBZ0VBQW9JQkFRRE1odGphdWlIS0hVdm8KanRPbWxqL1Fna2hBd3ZDd3BDU1ZIMFZrRzZlWXRjRFpYaEE5OTQvT1UrQzJDTUlGU0pnWis5aGE0NGtlcTV1aApmazJ5cXEvYlNiT3crK2FaeFRLWWdKU3Zad2dFRCtGSEFrM1VXbzJoUlk0L2taRGJFaTlRYlpGbkJLMXZIdzNTClQ4Y21jbVZEUHVCZCtZY0QwWnB5SVBpd2xXZTc3dVJoK3J3eWhLYWlsNVJXdmd3alcwT0lHYlR5anB1YlZwdG4KZEdWOVF1MHZveW05aTVOWnRQWDFxZnFzUm1CZGtPV1cyb25MakxEdHpRNUJOczA2ODdwaHZwUnBWT0hwc3k4OAo1YUk3Qm41TEdiY25zVDJXZkpsWGZKUFJtbkpjenMwVndGclpVQWIwcU0zdFBEcGw3Yy9JRlNKemxwd1Nob3lCCmZYdzhmRGtIQWdNQkFBRUNnZ0VBQVE1Wk1Oa3Y3eW5iWjdLZ3RHK0pUc2I1cTc5aWUyWEJWcTM1U0lqakZFdnEKc3JtbHVzMXZuTEU5RlQ3VS8yL2VYVU9ySUQya2NvekN1SE1GNm9GRjhLd0t3QndFdFJNR0Q1VGJVdEcxTFJXRwp6d29TM25Pc09kQ0UxVWFsUzluRmp6T2QzWkFtMnVldFVlMGNUK1pRQlBQamRFdzNkZElZN2JQNG9BMVhBY2ZMCldLWjRCdU9XQWlBNit6bVJ5ZTVOSkM5bHZ4QmhiZmk5VFYwOFBlcHM4RkRxUVVqeE0xRWRCZGVnekpRSTZ6R3MKcEFuMkxNb0E3c0Vra2hMZHg0VVJxZmlMR2FpWjIzM0J5dDFqVmlhM1JUSkhaREJUdEkycENqYldNbExsTWtjawplbm4zb0hsc0ZTMkVhK0xVb2VnNFloZUkyTktVdlh2cFMxR0FqelBBMFFLQmdRRC9FWDZFbjlSKzhoT1J0KzNhCjQ5VFMzNEJVL3BzNG1FTmJ3OS9vbmV5L0VqL3BpU0IvOFdnU2tKTzlENXpUYlF0bk84VXozaElTV0txaDh5Z2YKNnhWQXdhT1hyajk5dXJQckZoeTVTYVFTV21iR2NlZDJZbDgyc3c4aDh5eVVwY1FhaGlod3VvV2VQZU1QYXVaVgpGWVllSmRtOU0rUWJVajFqemJtNmFOazFRd0tCZ1FETlJoZllKT0tmYi95S3V5eUZsYnJTbENMbnh2ZktNaDVVCmNrZ1EzMlU2VDR3V0MxOWdLeTkzM29jR2lwM2JRR0htSkQ5Tk00SGNkWHRJZnVhSGpNbCtwMDVjcEJ3VG9FcS8KZTdDYnZhb0lCd1lKL1N4aU8yc1F5NzI5aUdYN1JDYUw5SXB0UGhGSFJSZkg4eUlYQXpmVUZDaGhZNDJLc3llVQpnTUhjcGE3TzdRS0JnQll0TFRUV3VLRDQ1eDZxUVRIZzBTWXNiSG4zVVFPUXFYcVhBeWEwWkZzUWVTSVMzQktECnFLckVpelBLNGJXNEsxK2tZTGJydmVKK2R3ZHhuckYzdlBkT1hxelhaNG5FNjlPcXJvQ2xtSEJJRDl0OUY4VEIKTk1vS3Myd2VPbWdLS1l5czBXTkx0RVpYMXlBR0NWU29kR3EybThISmV6R3M2czE4bTROTGptY0ZBb0dBSlFxSwo3dlBvK1FCS1AvWjZtRGhtbCswblIwKytFdnhzUUt6R21GVFhmV2d4VFNFTU90eFFHbjlMT2tEMUwwVTA1VVNSCkw2c2x1ZFJ4UktteGk3QTZBK0xJM3lxMTdreTBjRTB2bDByb1RiNkd0bks2K1pialFRcWkySHF1ZkdMVjJkZHMKeXoyeC9IeFpTRGwxWTFXdlUxTzMzYXNMTllZU2xGZVBvL001MGhrQ2dZRUFyR0o2WW1oVVl0RVdib3A1NkRzVwpoQXJOa0ZZdHUxN3lTaE1XWlQxbDU5VkJTY0wwbUFsRmF4OHdWL0pERXdjc2ZNaVdMelhhdDU3YXVZYlFGcGVtCjZ3MW8wbUM5VkhiRXlxRmZlc3BmNStQY2RiOU1zdHpaMnczcGJRWENXRUF2WFpHcWg5bmZBeCs3aFZJbEJXWjkKYzY4YXdFZXBja1RZaWMyMmZKVzdIeVU9Ci0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0K';
    protected const JWT_PUBLIC_KEY_BASE64 = 'LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUF6SWJZMnJvaHloMUw2STdUcHBZLwowSUpJUU1Md3NLUWtsUjlGWkJ1bm1MWEEyVjRRUGZlUHpsUGd0Z2pDQlVpWUdmdllXdU9KSHF1Ym9YNU5zcXF2CjIwbXpzUHZtbWNVeW1JQ1VyMmNJQkEvaFJ3Sk4xRnFOb1VXT1A1R1EyeEl2VUcyUlp3U3RieDhOMGsvSEpuSmwKUXo3Z1hmbUhBOUdhY2lENHNKVm51KzdrWWZxOE1vU21vcGVVVnI0TUkxdERpQm0wOG82Ym0xYWJaM1JsZlVMdApMNk1wdll1VFdiVDE5YW42ckVaZ1haRGxsdHFKeTR5dzdjME9RVGJOT3ZPNlliNlVhVlRoNmJNdlBPV2lPd1orClN4bTNKN0U5bG55WlYzeVQwWnB5WE03TkZjQmEyVkFHOUtqTjdUdzZaZTNQeUJVaWM1YWNFb2FNZ1gxOFBIdzUKQndJREFRQUIKLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0tCg==';
    protected const JWT_DEFAULT_LIFETIME_1_MINUTE = 60;
    protected const WEBDAV_URI_PATTERN = 'ms-word:ofe|u|https://testhost/documents-dav/%s/olcs/%s';

    protected const JWT_SUBJECT = 'user4574';
    protected const JWT_DOCUMENT = '0000000000000_OC00000000X_CoverLetter.rtf';

    protected WebDavJsonWebTokenGenerationService $sut;

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderDefaultLifetimeSecondsNotGreaterThanZero')]
    #[\PHPUnit\Framework\Attributes\Test]
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

    public static function dataProviderDefaultLifetimeSecondsNotGreaterThanZero(): array
    {
        return [
            'Zero (int)' => [0],
            'Negative Integer (int)' => [-1],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderWithInvalidPrivateKeyInvalidVariants')]
    #[\PHPUnit\Framework\Attributes\Test]
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

    public static function dataProviderWithInvalidPrivateKeyInvalidVariants(): array
    {
        return [
            'Base64 Encoded Positive Integer (int > string)' => [base64_encode('1')],
            'Base64 Encoded Negative Integer (int > string)' => [base64_encode('-1')],
            'FooBar (string)' => ['FooBar'],
            'Base64 Encoded Public Key (string)' => [static::JWT_PUBLIC_KEY_BASE64],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderWithInvalidPrivateKeyBase64OrPathInvalidVariants')]
    #[\PHPUnit\Framework\Attributes\Test]
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

    public static function dataProviderWithInvalidPrivateKeyBase64OrPathInvalidVariants(): array
    {
        return [
            'Base64 Encoded Zero (int > string)' => [base64_encode('0')],
            'Invalid Base64 characters (string)' => ['4rdHFh%2BHYoS8oLdVvbUzEVqB8Lvm7kSPnuwF0AAABYQ%3D'],
            'Non-Existent Path (string)' => ['/tmp/some-non-existent-path/to-some-file.pem'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function generateTokenIsCallable(): void
    {
        $this->sut = new WebDavJsonWebTokenGenerationService(
            static::JWT_PRIVATE_KEY_BASE64,
            static::JWT_DEFAULT_LIFETIME_1_MINUTE,
            self::WEBDAV_URI_PATTERN
        );
        $this->assertIsCallable($this->sut->generateToken(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('generateTokenIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function generateTokenWithPrivateKeyAsBase64ReturnsJsonWebToken(): void
    {
        $this->sut = new WebDavJsonWebTokenGenerationService(
            static::JWT_PRIVATE_KEY_BASE64,
            static::JWT_DEFAULT_LIFETIME_1_MINUTE,
            self::WEBDAV_URI_PATTERN
        );

        $result = $this->sut->generateToken(static::JWT_SUBJECT, static::JWT_DOCUMENT);
        $decodedJwt = JWT::decode($result, new Key(base64_decode((string) static::JWT_PUBLIC_KEY_BASE64, true), WebDavJsonWebTokenGenerationService::TOKEN_ALGORITHM));

        $this->assertObjectHasProperty(WebDavJsonWebTokenGenerationService::TOKEN_PAYLOAD_KEY_SUBJECT, $decodedJwt);
        $this->assertEquals(static::JWT_SUBJECT, $decodedJwt->sub);

        $this->assertObjectHasProperty(WebDavJsonWebTokenGenerationService::TOKEN_PAYLOAD_KEY_DOCUMENT, $decodedJwt);
        $this->assertEquals(static::JWT_DOCUMENT, $decodedJwt->doc);
    }

    #[\PHPUnit\Framework\Attributes\Depends('generateTokenIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function generateTokenWithPrivateKeyAsFileReturnsJsonWebToken(): void
    {
        file_put_contents(static::JWT_PRIVATE_KEY_TEMP_PATH, base64_decode((string) static::JWT_PRIVATE_KEY_BASE64, true), LOCK_EX);

        $this->sut = new WebDavJsonWebTokenGenerationService(
            static::JWT_PRIVATE_KEY_TEMP_PATH,
            static::JWT_DEFAULT_LIFETIME_1_MINUTE,
            self::WEBDAV_URI_PATTERN
        );

        $result = $this->sut->generateToken(static::JWT_SUBJECT, static::JWT_DOCUMENT);
        $decodedJwt = JWT::decode($result, new Key(base64_decode((string) static::JWT_PUBLIC_KEY_BASE64, true), WebDavJsonWebTokenGenerationService::TOKEN_ALGORITHM));

        $this->assertObjectHasProperty(WebDavJsonWebTokenGenerationService::TOKEN_PAYLOAD_KEY_SUBJECT, $decodedJwt);
        $this->assertEquals(static::JWT_SUBJECT, $decodedJwt->sub);

        $this->assertObjectHasProperty(WebDavJsonWebTokenGenerationService::TOKEN_PAYLOAD_KEY_DOCUMENT, $decodedJwt);
        $this->assertEquals(static::JWT_DOCUMENT, $decodedJwt->doc);
    }

    #[\PHPUnit\Framework\Attributes\Depends('generateTokenIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function generateTokenWithPrivateKeyAsBase64ReturnsJsonWebTokenWithIssuedAtAndExpiry(): void
    {
        $this->sut = new WebDavJsonWebTokenGenerationService(
            static::JWT_PRIVATE_KEY_BASE64,
            static::JWT_DEFAULT_LIFETIME_1_MINUTE,
            self::WEBDAV_URI_PATTERN
        );

        $result = $this->sut->generateToken(static::JWT_SUBJECT, static::JWT_DOCUMENT);
        $decodedJwt = JWT::decode($result, new Key(base64_decode((string) static::JWT_PUBLIC_KEY_BASE64, true), WebDavJsonWebTokenGenerationService::TOKEN_ALGORITHM));

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
