<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\GovUkAccount;

use Dvsa\GovUkAccount\Provider\GovUkAccount;
use Dvsa\Olcs\Api\Service\GovUkAccount\GovUkAccountService;
use Dvsa\Olcs\Api\Service\GovUkAccount\Response\GetAuthorisationUrlResponse;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class GovUkAccountServiceTest extends MockeryTestCase
{
    public const CONFIG = [
        'redirect_uri' => [
            'logged_in' => 'logged_in_uri',
        ],
        'keys' => [
            // Note keys below are generated ONLY for this Unit Test file and do not exist or used elsewhere
            'private_key' => 'LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JR0hBZ0VBTUJNR0J5cUdTTTQ5QWdFR0NDcUdTTTQ5QXdFSEJHMHdhd0lCQVFRZ01waWdmb01Rdi9jZEpWRmkKZ3EvZGFUdDUzV01IZlFPenlNZEVZOCt5YUkyaFJBTkNBQVJXMDFwN2pmQ1NUclJzMDk0UHk1YUtsd3k3L29OYQprZk03VEdtTmhTWWVFTndQelhJR1JQTWszbmtscVBBeHdBMXNFRXY3bC9sc3lGRUxqeDM1UnROYwotLS0tLUVORCBQUklWQVRFIEtFWS0tLS0t',
            'public_key' => 'LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUZrd0V3WUhLb1pJemowQ0FRWUlLb1pJemowREFRY0RRZ0FFVnROYWU0M3drazYwYk5QZUQ4dVdpcGNNdS82RApXcEh6TzB4cGpZVW1IaERjRDgxeUJrVHpKTjU1SmFqd01jQU5iQkJMKzVmNWJNaFJDNDhkK1ViVFhBPT0KLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0t',
            'algorithm' => 'ES256',
        ],
    ];

    protected GovUkAccount $provider;

    public function setUp(): void
    {
        $this->provider = m::mock(GovUkAccount::class);
    }

    /**
     * @throws \Exception
     */
    public function testCreateStateToken(): void
    {
        $sut = new GovUkAccountService(self::CONFIG, $this->provider);
        $data = [
            'property_1' => 'value_1',
            'property_2' => 'value_2',
        ];

        $currentTimestamp = time();
        $result = $sut->createStateToken($data, 60);

        $payload = json_decode(base64_decode(explode('.', $result)[1]), true);

        $this->assertArrayHasKey('property_1', $payload);
        $this->assertArrayHasKey('property_2', $payload);
        $this->assertArrayHasKey('jti', $payload);
        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('nbf', $payload);
        $this->assertArrayHasKey('exp', $payload);

        $this->assertEquals('value_1', $payload['property_1']);
        $this->assertEquals('value_2', $payload['property_2']);

        $this->assertMatchesRegularExpression('/^guka_state_[a-f0-9]{32}$/', $payload['jti']);

        $this->assertGreaterThanOrEqual($currentTimestamp, $payload['iat']);
        $this->assertGreaterThanOrEqual($currentTimestamp, $payload['nbf']);
        $this->assertGreaterThanOrEqual($currentTimestamp + 60, $payload['exp']);
    }

    public function testCreateStateTokenClaimsAreOverriddenByMethod(): void
    {
        $sut = new GovUkAccountService(self::CONFIG, $this->provider);
        $data = [
            'property_1' => 'value_1',
            'jti' => 'value_2',     // Should be overridden
            'exp' => 100,           // Should be overridden
        ];

        $currentTimestamp = time();
        $result = $sut->createStateToken($data, 60);

        $payload = json_decode(base64_decode(explode('.', $result)[1]), true);

        $this->assertArrayHasKey('property_1', $payload);
        $this->assertArrayHasKey('jti', $payload);
        $this->assertArrayHasKey('exp', $payload);

        $this->assertEquals('value_1', $payload['property_1']);
        $this->assertMatchesRegularExpression('/^guka_state_[a-f0-9]{32}$/', $payload['jti']);
        $this->assertGreaterThanOrEqual($currentTimestamp + 60, $payload['exp']);
    }

    public function testGetAuthorisationUrl(): void
    {
        $sut = new GovUkAccountService(self::CONFIG, $this->provider);

        $this->provider->expects('setState')->andReturn('some_state');
        $this->provider->expects('setNonce')->andReturn('some_nonce');
        $this->provider->expects('getState')->twice()->andReturn('some_state');
        $this->provider->expects('getNonce')->andReturn('some_nonce');
        $this->provider->expects('getAuthorizationUrl')->once()->with(
            m::on(function ($params) {
                $this->assertArrayHasKey('scope', $params);
                $this->assertArrayHasKey('redirect_uri', $params);
                $this->assertEquals($this->provider::DEFAULT_SCOPES, $params['scope']);
                $this->assertEquals('logged_in_uri', $params['redirect_uri']);
                return true;
            })
        )->andReturn('some_url');

        $result = $sut->getAuthorisationUrl('some_state', false);

        $this->assertInstanceOf(GetAuthorisationUrlResponse::class, $result);
        $this->assertEquals('some_url', $result->getUrl());
        $this->assertEquals('some_state', $result->getState());
        $this->assertEquals('some_nonce', $result->getNonce());
    }

    public function testGetAuthorisationUrlWithIdentityAssurance(): void
    {
        $sut = new GovUkAccountService(self::CONFIG, $this->provider);

        $this->provider->expects('setState')->andReturn('some_state');
        $this->provider->expects('setNonce')->andReturn('some_nonce');
        $this->provider->expects('getState')->twice()->andReturn('some_state');
        $this->provider->expects('getNonce')->andReturn('some_nonce');
        $this->provider->expects('getAuthorizationUrl')->once()->with(
            m::on(function ($params) {
                $this->assertArrayHasKey('scope', $params);
                $this->assertArrayHasKey('redirect_uri', $params);
                $this->assertArrayHasKey('vtr', $params);
                $this->assertArrayHasKey('claims', $params);
                return true;
            })
        )->andReturn('some_url');

        $result = $sut->getAuthorisationUrl('some_state', true);

        $this->assertInstanceOf(GetAuthorisationUrlResponse::class, $result);
        $this->assertEquals('some_url', $result->getUrl());
        $this->assertEquals('some_state', $result->getState());
        $this->assertEquals('some_nonce', $result->getNonce());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderMeetsVectorOfTrust')]
    public function testMeetsVectorOfTrust(mixed $actual, mixed $minimumConfidence, mixed $shouldPass): void
    {
        $result = GovUkAccountService::meetsVectorOfTrust($actual, $minimumConfidence);

        $this->assertEquals($shouldPass, $result);
    }

    public static function dataProviderMeetsVectorOfTrust(): array
    {
        return [
            'P0 meets P0' => [GovUkAccountService::VOT_P0, GovUkAccountService::VOT_P0, true],
            'P1 meets P0' => [GovUkAccountService::VOT_P1, GovUkAccountService::VOT_P0, true],
            'P2 meets P0' => [GovUkAccountService::VOT_P2, GovUkAccountService::VOT_P0, true],
            'P0 does not meet P1' => [GovUkAccountService::VOT_P0, GovUkAccountService::VOT_P1, false],
            'P1 meets P1' => [GovUkAccountService::VOT_P1, GovUkAccountService::VOT_P1, true],
            'P2 meets P1' => [GovUkAccountService::VOT_P2, GovUkAccountService::VOT_P1, true],
            'P0 does not meet P2' => [GovUkAccountService::VOT_P0, GovUkAccountService::VOT_P2, false],
            'P1 does not meet P2' => [GovUkAccountService::VOT_P1, GovUkAccountService::VOT_P2, false],
            'P2 meets P2' => [GovUkAccountService::VOT_P2, GovUkAccountService::VOT_P2, true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Depends('testMeetsVectorOfTrust')]
    public function testMeetsVectorOfTrustIsNotCaseSensitive(): void
    {
        $this->assertTrue(GovUkAccountService::meetsVectorOfTrust('p1', GovUkAccountService::VOT_P1));
        $this->assertTrue(GovUkAccountService::meetsVectorOfTrust(GovUkAccountService::VOT_P1, 'p1'));
    }

    public function testMeetsVectorOfTrustUnsupportedMinimumConfidenceThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        GovUkAccountService::meetsVectorOfTrust('p1', 'P9000');
    }

    public function testMeetsVectorOfTrustUnsupportedActualReturnsFalse(): void
    {
        $this->assertFalse(GovUkAccountService::meetsVectorOfTrust('P9000', GovUkAccountService::VOT_P0));
    }

    public function testProcessNamesWithEmptyArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(GovUkAccountService::ERR_MISSING_NAMES);
        GovUkAccountService::processNames([]);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpProcessNames')]
    public function testProcessNames(array $nameData, array $expectedOutput): void
    {
        $this->assertEquals($expectedOutput, GovUkAccountService::processNames($nameData));
    }

    public static function dpProcessNames(): array
    {
        return [
            'single record' => [
                [
                    0 => [
                        'validUntil' => null,
                        'nameParts' => [
                            0 => [
                                'type' => 'GivenName',
                                'value' => 'Given-Name-1',
                            ],
                            1 => [
                                'type' => 'FamilyName',
                                'value' => 'Family-Name',
                            ],
                            2 => [
                                'type' => 'GivenName',
                                'value' => 'Given-Name-2',
                            ],
                        ]
                    ],
                ],
                [
                    'firstName' => 'Given-Name-1 Given-Name-2',
                    'familyName' => 'Family-Name',
                ],
            ],
            'multiple records' => [
                [
                    0 => [
                        'validUntil' => '2021-12-25',
                        'nameParts' => [
                            0 => [
                                'type' => 'GivenName',
                                'value' => 'skipped',
                            ],
                            1 => [
                                'type' => 'FamilyName',
                                'value' => 'skipped',
                            ],
                        ],
                    ],
                    1 => [
                        'validUntil' => null,
                        'nameParts' => [
                            0 => [
                                'type' => 'GivenName',
                                'value' => 'Given-Name-1',
                            ],
                            1 => [
                                'type' => 'FamilyName',
                                'value' => 'Family-Name',
                            ],
                            2 => [
                                'type' => 'GivenName',
                                'value' => 'Given-Name-2',
                            ],
                        ],
                    ],
                    2 => [
                        'validUntil' => null,
                        'nameParts' => [
                            0 => [
                                'type' => 'GivenName',
                                'value' => 'ignored',
                            ],
                            1 => [
                                'type' => 'FamilyName',
                                'value' => 'ignored',
                            ],
                        ],
                    ],
                ],
                [
                    'firstName' => 'Given-Name-1 Given-Name-2',
                    'familyName' => 'Family-Name',
                ],
            ],
            'default to first record if all have valid until date' => [
                [
                    0 => [
                        'validUntil' => '2021-12-25',
                        'nameParts' => [
                            0 => [
                                'type' => 'GivenName',
                                'value' => 'Given-Name-1',
                            ],
                            1 => [
                                'type' => 'FamilyName',
                                'value' => 'Family-Name',
                            ],
                            2 => [
                                'type' => 'GivenName',
                                'value' => 'Given-Name-2',
                            ],
                        ],
                    ],
                    1 => [
                        'validUntil' => '2021-12-25',
                        'nameParts' => [
                            0 => [
                                'type' => 'GivenName',
                                'value' => 'skipped',
                            ],
                            1 => [
                                'type' => 'FamilyName',
                                'value' => 'skipped',
                            ],
                        ],
                    ],
                    2 => [
                        'validUntil' => '2021-12-25',
                        'nameParts' => [
                            0 => [
                                'type' => 'GivenName',
                                'value' => 'also skipped',
                            ],
                            1 => [
                                'type' => 'FamilyName',
                                'value' => 'also skipped',
                            ],
                        ],
                    ],
                ],
                [
                    'firstName' => 'Given-Name-1 Given-Name-2',
                    'familyName' => 'Family-Name',
                ],
            ],
        ];
    }
}
