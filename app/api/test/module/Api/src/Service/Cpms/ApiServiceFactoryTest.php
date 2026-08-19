<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Cpms;

use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Service\AccessToken\Provider;
use Dvsa\Olcs\Api\Service\Cpms\ApiServiceFactory;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Dvsa\Olcs\Cpms\Service\ApiService;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;

final class ApiServiceFactoryTest extends TestCase
{
    public function testInvokeToggleOff(): void
    {
        $mockSl = $this->mockContainer(self::baseConfig(), toggleEnabled: false);

        $sut = new ApiServiceFactory();
        $apiService = $sut->__invoke($mockSl, ApiService::class);

        $this->assertInstanceOf(ApiService::class, $apiService);
        $this->assertFalse($apiService->getHttpClient()->hasGatewayTokenProvider());
        $this->assertSame('api.cpms.domain', $apiService->getOptions()->getDomain());
        $this->assertNull($apiService->getOptions()->getProxy());
    }

    public function testInvokeToggleOnBuildsGatewayClient(): void
    {
        $config = self::baseConfig();
        $config['cpms_api']['gateway'] = [
            'domain' => 'gw.cpms.domain',
            'proxy' => 'http://proxy.local:3128',
            'oauth2' => [
                'client_id' => 'an-entra-client-id',
                'client_secret' => 'an-entra-secret',
                'token_url' => 'https://login.example.com/token',
                'scope' => 'api://an-app/.default',
                'proxy' => 'http://proxy.local:3128',
                'service_name' => 'CPMS Hybrid Gateway',
            ],
        ];

        $mockSl = $this->mockContainer($config, toggleEnabled: true);
        $mockSl->shouldReceive('build')
            ->with(Provider::class, $config['cpms_api']['gateway']['oauth2'])
            ->andReturn(m::mock(Provider::class));

        $sut = new ApiServiceFactory();
        $apiService = $sut->__invoke($mockSl, ApiService::class);

        $this->assertInstanceOf(ApiService::class, $apiService);
        $this->assertTrue($apiService->getHttpClient()->hasGatewayTokenProvider());
        $this->assertSame('gw.cpms.domain', $apiService->getOptions()->getDomain());
        $this->assertSame('http://proxy.local:3128', $apiService->getOptions()->getProxy());
    }

    public function testInvokeToggleOnWithoutGatewayConfigThrows(): void
    {
        $mockSl = $this->mockContainer(self::baseConfig(), toggleEnabled: true);

        $sut = new ApiServiceFactory();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('cpms_api.gateway config is missing');
        $sut->__invoke($mockSl, ApiService::class);
    }

    /**
     * Parameter Store cannot hold an empty string, so an environment that has not yet been issued
     * Entra credentials carries " " sentinels. Those must be rejected as firmly as a missing key.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpUnusableGatewayValues')]
    public function testInvokeToggleOnWithUnusableGatewayValueThrows(
        array $gatewayConfig,
        string $expectedKey
    ): void {
        $config = self::baseConfig();
        $config['cpms_api']['gateway'] = $gatewayConfig;

        $mockSl = $this->mockContainer($config, toggleEnabled: true);

        $sut = new ApiServiceFactory();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($expectedKey);
        $sut->__invoke($mockSl, ApiService::class);
    }

    public static function dpUnusableGatewayValues(): array
    {
        return [
            'blank domain' => [self::gatewayConfig(['domain' => ' ']), 'domain'],
            'blank client id' => [self::gatewayConfig(['oauth2' => ['client_id' => ' ']]), 'oauth2.client_id'],
            'blank client secret' => [self::gatewayConfig(['oauth2' => ['client_secret' => ' ']]), 'oauth2.client_secret'],
            'blank token url' => [self::gatewayConfig(['oauth2' => ['token_url' => ' ']]), 'oauth2.token_url'],
            'blank scope' => [self::gatewayConfig(['oauth2' => ['scope' => ' ']]), 'oauth2.scope'],
            'missing oauth2 block' => [['domain' => 'gw.cpms.domain'], 'oauth2.client_id'],
        ];
    }

    public function testInvokeToggleOnNamesEveryUnusableKey(): void
    {
        $config = self::baseConfig();
        $config['cpms_api']['gateway'] = self::gatewayConfig([
            'oauth2' => ['client_id' => ' ', 'token_url' => ' ', 'scope' => ' '],
        ]);

        $mockSl = $this->mockContainer($config, toggleEnabled: true);

        $sut = new ApiServiceFactory();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('oauth2.client_id, oauth2.token_url, oauth2.scope');
        $sut->__invoke($mockSl, ApiService::class);
    }

    /**
     * A fully populated gateway block, with $overrides merged one level into oauth2.
     */
    private static function gatewayConfig(array $overrides = []): array
    {
        $gateway = [
            'domain' => 'gw.cpms.domain',
            'proxy' => 'http://proxy.local:3128',
            'oauth2' => [
                'client_id' => 'an-entra-client-id',
                'client_secret' => 'an-entra-secret',
                'token_url' => 'https://login.example.com/token',
                'scope' => 'an-app/.default',
                'proxy' => 'http://proxy.local:3128',
                'service_name' => 'CPMS Hybrid Gateway',
            ],
        ];

        $gateway['oauth2'] = array_merge($gateway['oauth2'], $overrides['oauth2'] ?? []);
        unset($overrides['oauth2']);

        return array_merge($gateway, $overrides);
    }

    private function mockContainer(array $config, bool $toggleEnabled): m\MockInterface
    {
        $mockAuth = m::mock(AuthorizationService::class);
        $mockAuth->shouldReceive('getIdentity->getUser->getId')->andReturn('2');

        $mockToggle = m::mock(ToggleService::class);
        $mockToggle->shouldReceive('isEnabled')
            ->with(FeatureToggle::CPMS_HYBRID_GATEWAY)
            ->andReturn($toggleEnabled);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config)
            ->shouldReceive('get')->with(AuthorizationService::class)->andReturn($mockAuth)
            ->shouldReceive('get')->with('Logger')->andReturn(new NullLogger())
            ->shouldReceive('get')->with(ToggleService::class)->andReturn($mockToggle);

        return $mockSl;
    }

    private static function baseConfig(): array
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
}
