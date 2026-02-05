<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Service;

use Dvsa\Olcs\Cpms\Authenticate\CpmsIdentityProvider;
use Dvsa\Olcs\Cpms\Client\ClientOptions;
use Dvsa\Olcs\Cpms\Client\HttpClient;
use Dvsa\Olcs\Cpms\Service\ApiService;
use Dvsa\Olcs\Cpms\Service\ApiServiceFactory;
use PHPUnit\Framework\TestCase;

class ApiServiceFactoryTest extends TestCase
{
    public function testCreateApiService(): void
    {
        $config = [
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

        $userId = '123';

        $sut = new ApiServiceFactory($config, $userId);
        $apiService = $sut->createApiService();

        $this->assertInstanceOf(ApiService::class, $apiService);
        $this->assertEquals(2, $apiService->getOptions()->getVersion());
        $this->assertInstanceOf(ClientOptions::class, $apiService->getOptions());
        $this->assertInstanceOf(HttpClient::class, $apiService->getHttpClient());
        $this->assertInstanceOf(CpmsIdentityProvider::class, $apiService->getIdentity());
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

        $sut = new ApiServiceFactory($config, $userId);
        $sut->createApiService();
    }

    public static function dpTestCreateApiServiceExceptionsThrown(): array
    {
        return [
            'no-credentials' => [
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

            ],
            'no-client_id' => [
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

            ],
            'no-client_secret' => [
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

            ],
            'no-options' => [
                [
                    'credentials' => [
                        'client_id' => 'a-client-id',
                        'client_secret' => null,
                    ],
                    'clientOptions' => [
                        'options' => null
                    ]
                ],

            ],
        ];
    }
}
