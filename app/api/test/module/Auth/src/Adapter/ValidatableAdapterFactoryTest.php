<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Adapter;

use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\LoginFactory;
use Dvsa\Olcs\Auth\Adapter\ValidatableAdapterFactory;
use InvalidArgumentException;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\OlcsTest\MocksServicesTrait;
use Mockery as m;
use stdClass;

/**
 * @see \Dvsa\Olcs\Auth\Adapter\ValidatableAdapterFactory
 */
class ValidatableAdapterFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    public const CONFIG_WITHOUT_NAMESPACE = [];
    public const CONFIG_WITHOUT_DEFAULT_ADAPTER_DEFINED = [
        LoginFactory::CONFIG_NAMESPACE => [],
    ];
    public const CONFIG_WITH_ADAPTER_NOT_DEFINED = [
        LoginFactory::CONFIG_NAMESPACE => [
            LoginFactory::AUTH_CONFIG_DEFAULT_ADAPTER => 'some_adapter',
            LoginFactory::AUTH_CONFIG_ADAPTERS => []
        ]
    ];
    public const CONFIG_WITH_ADAPTER_DEFINED_AND_ADAPTER_CONFIG_ADAPTER_NOT_DEFINED = [
        LoginFactory::CONFIG_NAMESPACE => [
            LoginFactory::AUTH_CONFIG_DEFAULT_ADAPTER => 'some_adapter',
            LoginFactory::AUTH_CONFIG_ADAPTERS => [
                'some_adapter' => []
            ]
        ]
    ];
    public const CONFIG_WITH_ADAPTER_DEFINED = [
        LoginFactory::CONFIG_NAMESPACE => [
            LoginFactory::AUTH_CONFIG_DEFAULT_ADAPTER => 'some_adapter',
            LoginFactory::AUTH_CONFIG_ADAPTERS => [
                'some_adapter' => [
                    LoginFactory::ADAPTER_CONFIG_ADAPTER => ValidatableAdapterInterface::class,
                ]
            ]
        ]
    ];

    /**
     * @var ValidatableAdapterFactory
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
    public function invokeReturnsAnInstanceOfValidatableAdapterInterfaceWhenAdapterDefinedAsInstancedClass(): void
    {
        // Setup
        $this->setUpSut();
        $this->configureAdapter($expectedAdapter = $this->getValidatableAdapterMock());

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertSame($expectedAdapter, $result, 'Provided adapter does not match the expectedAdapter instance configured');
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsAnInstanceOfValidatableAdapterInterfaceWhenAdapterDefinedAsClassReferenceStringAndExistsInServiceManager(): void
    {
        // Setup
        $this->setUpSut();
        $this->configureAdapter('foo');
        $this->serviceManager()->setService('foo', $expectedAdapter = $this->getValidatableAdapterMock());

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertSame($expectedAdapter, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsAnInstanceOfValidatableAdapterInterfaceDefinedAsClassReferenceStringAndDoesNotExistInServiceManagerAndInstantiates(): void
    {
        // Setup
        $this->setUpSut();
        $this->configureAdapter(ValidatableAdapterInterface::class);

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(ValidatableAdapterInterface::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeThrowsExceptionWhenConfigAuthNamespaceNotDefined(): void
    {
        // Setup
        $this->setUpSut();
        $this->configService(static::CONFIG_WITHOUT_NAMESPACE);

        // Expectations
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Config namespace is not defined: ' . LoginFactory::CONFIG_NAMESPACE);

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeThrowsExceptionWhenConfigAuthNamespaceNotDefined')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeThrowsExceptionWhenConfigAuthDefaultAdapterNotDefined(): void
    {
        // Setup
        $this->setUpSut();
        $this->configService(static::CONFIG_WITHOUT_DEFAULT_ADAPTER_DEFINED);

        // Expectations
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Default adapter not defined: ' . ValidatableAdapterFactory::AUTH_CONFIG_DEFAULT_ADAPTER);

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeThrowsExceptionWhenConfigAuthDefaultAdapterNotDefined')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeThrowsExceptionWhenConfigAuthAdapterNotDefined(): void
    {
        // Setup
        $this->setUpSut();
        $this->configService(static::CONFIG_WITH_ADAPTER_NOT_DEFINED);

        // Expectations
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing config for default adapter: some_adapter');

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeThrowsExceptionWhenConfigAuthAdapterNotDefined')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeThrowsExceptionWhenConfigAuthAdapterDefinedAndAdapterConfigAdapterNotDefined(): void
    {
        // Setup
        $this->setUpSut();
        $this->configService(static::CONFIG_WITH_ADAPTER_DEFINED_AND_ADAPTER_CONFIG_ADAPTER_NOT_DEFINED);

        // Expectations
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Adaptor class is not defined in the adapter configuration for adaptor: some_adapter');

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeThrowsExceptionWhenConfigAuthAdapterDefinedAndAdapterConfigAdapterNotDefined')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeThrowsExceptionWhenConfigAuthAdapterNotInstanceOfValidatableAdapterInterface(): void
    {
        // Setup
        $this->setUpSut();
        $this->configureAdapter(new stdClass());

        // Expectations
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Defined adapter is not instance of ' . \Laminas\Authentication\Adapter\ValidatableAdapterInterface::class);

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new ValidatableAdapterFactory();
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->configService();
    }

    /**
     * @param array|null $config
     * @return array
     */
    protected function configService(?array $config = null): array
    {
        if (! $this->serviceManager->has('config')) {
            $this->serviceManager->setService('config', static::CONFIG_WITH_ADAPTER_DEFINED);
        }

        if (! is_null($config)) {
            $this->serviceManager->setService('config', $config);
        }

        return $this->serviceManager->get('config');
    }

    /**
     * @param null $adapter
     */
    protected function configureAdapter(mixed $adapter = null): void
    {
        $config = static::CONFIG_WITH_ADAPTER_DEFINED;
        $config[LoginFactory::CONFIG_NAMESPACE][LoginFactory::AUTH_CONFIG_ADAPTERS]['some_adapter'][LoginFactory::ADAPTER_CONFIG_ADAPTER] = $adapter;
        $this->configService($config);
    }

    /**
     * @return ValidatableAdapterInterface
     */
    protected function getValidatableAdapterMock(): ValidatableAdapterInterface
    {
        return $this->createMock(ValidatableAdapterInterface::class);
    }
}
