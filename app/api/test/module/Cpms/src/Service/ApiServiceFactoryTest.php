<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Service;

use Dvsa\Olcs\Cpms\Authenticate\CpmsIdentityProvider;
use Dvsa\Olcs\Cpms\Authenticate\GatewayTokenProviderInterface;
use Dvsa\Olcs\Cpms\Client\ClientOptions;
use Dvsa\Olcs\Cpms\Client\HttpClient;
use Dvsa\Olcs\Cpms\Service\ApiService;
use Dvsa\Olcs\Cpms\Service\ApiServiceFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Log\NullLogger;

final class ApiServiceFactoryTest extends TestCase
{
    public function testCreateApiService(): void
    {
        $config = self::legacyConfig();
        $userId = '123';

        $sut = new ApiServiceFactory($config, $userId, new NullLogger());
        $apiService = $sut->createApiService();

        $this->assertInstanceOf(ApiService::class, $apiService);
        $this->assertSame(2, $apiService->getOptions()->getVersion());
        $this->assertInstanceOf(ClientOptions::class, $apiService->getOptions());
        $this->assertInstanceOf(HttpClient::class, $apiService->getHttpClient());
        $this->assertInstanceOf(CpmsIdentityProvider::class, $apiService->getIdentity());
    }

    public function testCreateApiServiceWithGatewayTokenProviderAndProxy(): void
    {
        $config = [
            'cpms_api' => [
                'rest_client' => [
                    'options' => [
                        'version' => 2,
                        'domain' => 'gw.cpms.domain',
                        'grant_type' => 'client_credentials',
                        'timeout' => 15.0,
                        'headers' => [
                            'Accept' => 'application/json',
                        ],
                        'proxy' => 'http://proxy.local:3128',
                    ],
                ],
            ],
            'cpms_credentials' => [
                'client_id' => 'a-client-id',
                'client_secret' => 'a-client-secret',
            ],
        ];

        $tokenProvider = m::mock(GatewayTokenProviderInterface::class);

        $sut = new ApiServiceFactory($config, '123', new NullLogger(), $tokenProvider);
        $apiService = $sut->createApiService();

        $this->assertTrue($apiService->getHttpClient()->hasGatewayTokenProvider());
        $this->assertSame('http://proxy.local:3128', $apiService->getOptions()->getProxy());
        $this->assertSame('gw.cpms.domain', $apiService->getOptions()->getDomain());
    }

    public function testCreateApiServiceWithoutGatewayDefaultsToLegacy(): void
    {
        // reuse the existing happy-path $config shape from testCreateApiService
        $sut = new ApiServiceFactory(self::legacyConfig(), '123', new NullLogger());
        $apiService = $sut->createApiService();

        $this->assertFalse($apiService->getHttpClient()->hasGatewayTokenProvider());
        $this->assertNull($apiService->getOptions()->getProxy());
    }

    private static function legacyConfig(): array
    {
        return [
            'cpms_api' => [
                'rest_client' => [
                    'options' => [
                        'version' => 2,
                        'domain' => 'api.cpms.domain',
                        'client_id' => 'some-client-id',
                        'client_secret' => 'some-secret',
                        'customer_reference' => 'some-customer-ref',
                        'grant_type' => 'client_credentials',
                        'timeout' => 15.0,
                        'headers' => [
                            'Accept' => 'application/json',
                        ],

                    ],
                ],
            ],
            'log' => [
                'Logger' => [
                    'writers' => [
                        'full' => [
                            'options' => [
                                'stream' => '/var/tmp/backend.log',
                                'filters' => [
                                    'priority' => [
                                        'name' => 'priority',
                                        'options' => [
                                            'priority' => 1,
                                        ]
                                    ],
                                ]
                            ],
                        ]
                    ]
                ],
            ],
            'cpms_credentials' => [
                'client_id' => 'a-client-id',
                'client_secret' => 'a-client-secret',
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCreateApiServiceExceptionsThrown')]
    public function testCreateApiServiceExceptionsThrown(mixed $dpData): void
    {
        $config = [
            'cpms_api' => [
                'rest_client' => $dpData['clientOptions']
            ],
            'log' => [
                'Logger' => [
                    'writers' => [
                        'full' => [
                            'options' => [
                                'stream' => '/var/tmp/backend.log',
                                'filters' => [
                                    'priority' => [
                                        'name' => 'priority',
                                        'options' => [
                                            'priority' => 1,
                                        ]
                                    ],
                                ]
                            ],
                        ]
                    ]
                ],
            ],
            'cpms_credentials' => $dpData['credentials']
        ];

        $userId = '123';

        $this->expectException(\RuntimeException::class);

        $sut = new ApiServiceFactory($config, $userId, new NullLogger());
        $sut->createApiService();
    }

    public static function dpTestCreateApiServiceExceptionsThrown(): \Iterator
    {
        yield 'no-credentials' => [
            [
                'credentials' => null,
                'clientOptions' => [
                    'options' => [
                        'version' => 2,
                        'domain' => 'api.cpms.domain',
                        'client_id' => 'some-client-id',
                        'client_secret' => 'some-secret',
                        'customer_reference' => 'some-customer-ref',
                        'grant_type' => 'client_credentials',
                        'timeout' => 15.0,
                        'headers' => [
                            'Accept' => 'application/json',
                        ],

                    ],
                ]
            ],

        ];
        yield 'no-client_id' => [
            [
                'credentials' => [
                    'client_id' => null,
                    'client_secret' => 'a-client-secret',
                ],
                'clientOptions' => [
                    'options' => [
                        'version' => 2,
                        'domain' => 'api.cpms.domain',
                        'client_id' => 'some-client-id',
                        'client_secret' => 'some-secret',
                        'customer_reference' => 'some-customer-ref',
                        'grant_type' => 'client_credentials',
                        'timeout' => 15.0,
                        'headers' => [
                            'Accept' => 'application/json',
                        ],

                    ],
                ]
            ],

        ];
        yield 'no-client_secret' => [
            [
                'credentials' => [
                    'client_id' => 'a-client-id',
                    'client_secret' => null,
                ],
                'clientOptions' => [
                    'options' => [
                        'version' => 2,
                        'domain' => 'api.cpms.domain',
                        'client_id' => 'some-client-id',
                        'client_secret' => 'some-secret',
                        'customer_reference' => 'some-customer-ref',
                        'grant_type' => 'client_credentials',
                        'timeout' => 15.0,
                        'headers' => [
                            'Accept' => 'application/json',
                        ],

                    ],
                ]
            ],

        ];
        yield 'no-options' => [
            [
                'credentials' => [
                    'client_id' => 'a-client-id',
                    'client_secret' => null,
                ],
                'clientOptions' => [
                    'options' => null
                ]
            ],

        ];
    }
}
