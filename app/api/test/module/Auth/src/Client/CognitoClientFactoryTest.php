<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Client;

use Dvsa\Authentication\Cognito\Client;
use Dvsa\Olcs\Auth\Client\CognitoClientFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\OlcsTest\MocksServicesTrait;

/**
 * Class CognitoClientFactoryTest
 * @see CognitoClientFactory
 */
class CognitoClientFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    public const CONFIG_WITH_WITH_VALID_SETTINGS = [
        CognitoClientFactory::CONFIG_CLIENT_ID => 'client_id',
        CognitoClientFactory::CONFIG_CLIENT_SECRET => 'client_secret',
        CognitoClientFactory::CONFIG_POOL_ID => 'pool_id',
        CognitoClientFactory::CONFIG_REGION => 'region',
        CognitoClientFactory::CONFIG_NBF_LEEWAY => 2,
        CognitoClientFactory::CONFIG_HTTP => [],
    ];

    /**
     * @var CognitoClientFactory
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->__invoke(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsAnInstanceOfClient(): void
    {
        // Setup
        $this->setUpSut();
        $this->configService(static::CONFIG_WITH_WITH_VALID_SETTINGS);

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(Client::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeThrowsExceptionWhenConfigCognitoNamespaceNotDefined(): void
    {
        // Setup
        $this->setUpSut();
        $this->configService([]);

        // Expectations
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(CognitoClientFactory::EXCEPTION_MESSAGE_NAMESPACE_MISSING);

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\DataProvider('incorrectSettingsProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeThrowsExceptionWhenConfigSettingsNotDefined(array $config): void
    {
        // Setup
        $this->setUpSut();
        $this->configService($config);

        // Expectations
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(CognitoClientFactory::EXCEPTION_MESSAGE_OPTION_MISSING);

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new CognitoClientFactory();
    }

    public static function incorrectSettingsProvider(): array
    {
        return [
            'Missing clientId' => [
                CognitoClientFactory::CONFIG_ADAPTER => [
                    CognitoClientFactory::CONFIG_CLIENT_SECRET => 'client_secret',
                    CognitoClientFactory::CONFIG_POOL_ID => 'pool_id',
                    CognitoClientFactory::CONFIG_REGION => 'region',
                ]
            ],
            'Missing clientSecret' => [
                CognitoClientFactory::CONFIG_ADAPTER => [
                    CognitoClientFactory::CONFIG_CLIENT_ID => 'client_id',
                    CognitoClientFactory::CONFIG_POOL_ID => 'pool_id',
                    CognitoClientFactory::CONFIG_REGION => 'region',
                ]
            ],
            'Missing poolId' => [
                CognitoClientFactory::CONFIG_ADAPTER => [
                    CognitoClientFactory::CONFIG_CLIENT_ID => 'client_id',
                    CognitoClientFactory::CONFIG_CLIENT_SECRET => 'client_secret',
                    CognitoClientFactory::CONFIG_REGION => 'region',
                ]
            ],
            'Missing region' => [
                CognitoClientFactory::CONFIG_ADAPTER => [
                    CognitoClientFactory::CONFIG_CLIENT_ID => 'client_id',
                    CognitoClientFactory::CONFIG_CLIENT_SECRET => 'client_secret',
                    CognitoClientFactory::CONFIG_POOL_ID => 'pool_id',
                ]
            ],
            'Missing http' => [
                CognitoClientFactory::CONFIG_ADAPTER => [
                    CognitoClientFactory::CONFIG_CLIENT_ID => 'client_id',
                    CognitoClientFactory::CONFIG_CLIENT_SECRET => 'client_secret',
                    CognitoClientFactory::CONFIG_POOL_ID => 'pool_id',
                    CognitoClientFactory::CONFIG_REGION => 'region'
                ]
            ]
        ];
    }

    /**
     * @param array|null $config
     */
    protected function configService(?array $config = null): void
    {
        $config = [
            CognitoClientFactory::CONFIG_NAMESPACE => [
                CognitoClientFactory::CONFIG_ADAPTERS => [
                    CognitoClientFactory::CONFIG_ADAPTER => $config
                ]
            ]
        ];

        $this->serviceManager->setService('config', $config);
    }
}
