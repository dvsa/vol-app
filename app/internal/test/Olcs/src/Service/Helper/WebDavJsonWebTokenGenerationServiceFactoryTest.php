<?php

namespace OlcsTest\Service\Helper;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;
use Laminas\ServiceManager\ServiceManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationService;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationServiceFactory;
use Mockery as m;
use Psr\Container\ContainerInterface;

/**
 * @covers \Olcs\Service\Helper\WebDavJsonWebTokenGenerationService
 */
class WebDavJsonWebTokenGenerationServiceFactoryTest extends MockeryTestCase
{
    protected const JWT_PRIVATE_KEY_BASE64 = 'LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQpNSUlCT3dJQkFBSkJBSnRybTk2M3BYNlJIdG1oWTdGSndlTUcrWDU4bWMwbzRjUTlOMmp3SmVLWFlnM2h3bEpWCkVkTTByM1d6Y0FVcVhHeStvNlpWVGF5N3NnRmdTM1kvbVZVQ0F3RUFBUUpCQUkwdkxjTWVOTHBLL2lsWTBJVW0KcVhpZ3gxZzl2RUdBbDhaNmpiRklKa0kxTU45bEZmRVNMSHJWQTNKck1KZEh2R3RIN2ZoSHNoaUM1LzR1SDVpbAorU2tDSVFEa2dBYjJveThNMkUwQ05FbEJpbWhwTzN4MWV5bTNxNStPR0NZeEZHckxWd0loQUs0Z0IvMytodlpICk5SNm1rUldONktCRC95ZDMzaDFJa0djNmFXTTBhRUV6QWlFQWxQdE1qdjZ5cktOVEFuN296SXpicXRFWVF0ajgKeUQ1a0Y1ZHpQMGphb0owQ0lENWFJZ0tHSG5ZYVVaOUVMamYxdFJPT3hkT3dUTTFYcXI0TVlLaXhuNU9aQWlCOApaQkNaTG41dTRuWEFpZW1ENHA3bkF5dWp4azlQcWdBQmxUbXBJRHE1clE9PQotLS0tLUVORCBSU0EgUFJJVkFURSBLRVktLS0tLQ==';
    protected const JWT_DEFAULT_LIFETIME_1_MINUTE = 60;
    protected const URL_PATTERN = 'ms-word:ofe|u|https://testhost/documents-dav/%s/olcs/%s';

    protected const CONFIG_VALID = [
        WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_NAMESPACE => [
            WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_DEFAULT_LIFETIME_SECONDS => self::JWT_DEFAULT_LIFETIME_1_MINUTE,
            WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_PRIVATE_KEY => self::JWT_PRIVATE_KEY_BASE64,
            WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_URL_PATTERN => self::URL_PATTERN,
        ]
    ];
    protected const CONFIG_INVALID_EMPTY_PRIVATE_KEY = [
        WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_NAMESPACE => [
            WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_DEFAULT_LIFETIME_SECONDS => self::JWT_DEFAULT_LIFETIME_1_MINUTE,
            WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_PRIVATE_KEY => '',
        ]
    ];
    protected const CONFIG_INVALID_UNDEFINED_PRIVATE_KEY = [
        WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_NAMESPACE => [
            WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_DEFAULT_LIFETIME_SECONDS => self::JWT_DEFAULT_LIFETIME_1_MINUTE,
        ]
    ];
    protected const CONFIG_INVALID_EMPTY_DEFAULT_LIFETIME_SECONDS = [
        WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_NAMESPACE => [
            WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_DEFAULT_LIFETIME_SECONDS => '',
            WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_PRIVATE_KEY => self::JWT_PRIVATE_KEY_BASE64,
        ]
    ];
    protected const CONFIG_INVALID_UNDEFINED_DEFAULT_LIFETIME_SECONDS = [
        WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_NAMESPACE => [
            WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_PRIVATE_KEY => self::JWT_PRIVATE_KEY_BASE64,
        ]
    ];
    protected const CONFIG_INVALID_UNDEFINED_URL_PATTERN = [
        WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_NAMESPACE => [
            WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_DEFAULT_LIFETIME_SECONDS => self::JWT_DEFAULT_LIFETIME_1_MINUTE,
            WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_PRIVATE_KEY => self::JWT_PRIVATE_KEY_BASE64
        ]
    ];

    protected WebDavJsonWebTokenGenerationServiceFactory $sut;

    protected function setUp(): void
    {
        $this->serviceManager = $this->createMock(ContainerInterface::class);
    }

    /**
     * @test
     */
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, '__invoke']);
    }

    /**
     * @test
     * @depends __invoke_IsCallable
     */
    public function invokeReturnsAnInstanceOfWebDavJsonWebTokenGenerationService()
    {
        // Setup
        $sm = $this->createMock(ContainerInterface::class);
        $sm->method('get')->with('Config')->willReturn(static::CONFIG_VALID);

        $this->setUpSut();

        // Execute
        $result = $this->sut->__invoke($sm, null);

        // Assert
        $this->assertInstanceOf(WebDavJsonWebTokenGenerationService::class, $result);
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeThrowsExceptionWhenPrivateKeyEmpty()
    {
        // Setup
        $sm = $this->createMock(ContainerInterface::class);
        $sm->method('get')->with('Config')->willReturn(static::CONFIG_INVALID_EMPTY_PRIVATE_KEY);

        $this->setUpSut();

        // Expect
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(0x20);
        $this->expectExceptionMessage('Config key private_key: value must be set and not empty()');

        // Execute
        $result = $this->sut->__invoke($sm, null);
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeThrowsExceptionWhenPrivateNotSet()
    {
        // Setup
        $sm = $this->createMock(ContainerInterface::class);
        $sm->method('get')->with('Config')->willReturn(static::CONFIG_INVALID_UNDEFINED_PRIVATE_KEY);

        $this->setUpSut();

        // Expect
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(0x20);
        $this->expectExceptionMessage('Config key private_key: value must be set and not empty()');

        // Execute
        $result = $this->sut->__invoke($sm, null);
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeThrowsExceptionWhenDefaultLifetimeSecondsEmpty()
    {
        // Setup
        $sm = $this->createMock(ContainerInterface::class);
        $sm->method('get')->with('Config')->willReturn(static::CONFIG_INVALID_EMPTY_DEFAULT_LIFETIME_SECONDS);

        $this->setUpSut();

        // Expect
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(0x10);
        $this->expectExceptionMessage('Config key default_lifetime_seconds: value must be set and not empty()');

        // Execute
        $result = $this->sut->__invoke($sm, null);
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeThrowsExceptionWhenDefaultLifetimeSecondsNotSet()
    {
        // Setup
        $sm = $this->createMock(ContainerInterface::class);
        $sm->method('get')->with('Config')->willReturn(static::CONFIG_INVALID_UNDEFINED_DEFAULT_LIFETIME_SECONDS);

        $this->setUpSut();

        // Expect
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(0x10);
        $this->expectExceptionMessage('Config key default_lifetime_seconds: value must be set and not empty()');

        // Execute
        $result = $this->sut->__invoke($sm, null);
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeThrowsExceptionWhenUrlPatternNotSet()
    {
        // Setup
        $sm = $this->createMock(ContainerInterface::class);
        $sm->method('get')->with('Config')->willReturn(static::CONFIG_INVALID_UNDEFINED_URL_PATTERN);

        $this->setUpSut();

        // Expect
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(0x30);
        $this->expectExceptionMessage('Config key url_pattern: value must be set and not empty()');

        // Execute
        $result = $this->sut->__invoke($sm, null);
    }

    protected function setUpSut(): void
    {
        $this->sut = new WebDavJsonWebTokenGenerationServiceFactory();
    }
}
