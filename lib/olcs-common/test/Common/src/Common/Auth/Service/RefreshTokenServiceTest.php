<?php

declare(strict_types=1);

namespace CommonTest\Common\Auth\Service;

use Common\Auth\Service\RefreshTokenService;
use Common\Auth\Service\RefreshTokenServiceFactory;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Response;
use Common\Test\MocksServicesTrait;
use Exception;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\ServiceManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Mockery as m;

/**
 * @see RefreshTokenService
 */
class RefreshTokenServiceTest extends MockeryTestCase
{
    use MocksServicesTrait;

    private RefreshTokenService $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->setUpServiceManager();
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $this->commandSender();
        return $serviceManager;
    }

    protected function setUpSut(): RefreshTokenService
    {
        return new RefreshTokenService($this->commandSender());
    }

    /**
     * @test
     * @dataProvider isRefreshRequiredProvider
     */
    public function isRefreshRequiredReturnsExpectedResult(array $token, bool $expectedResult): void
    {
        $this->markTestSkipped('Works locally, not in pipeline (it did for a while). Investigation required.');

        // Setup
        $this->sut = $this->setUpSut();

        // Execute
        $result = $this->sut->isRefreshRequired($token);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function isRefreshRequiredProvider(): array
    {
        return [
            [['expires' => time() - 70], true],
            [['expires' => time() + 70], false],
            [['expires' => time() - 10], true],
            [['expires' => time() + 10], true],
            [['expires' => time()], true],
        ];
    }

    /**
     * @test
     */
    public function refreshTokensReturnsExpectedResult(): void
    {
        // Setup
        $this->sut = $this->setUpSut();
        $token = ['refresh_token' => 'refresh_token'];
        $identifier = 'username';

        $result = $this->response(
            true,
            [
                'flags' => [
                    'isValid' => true,
                    'identity' => [
                        'token' => 'newToken'
                    ]
                ]
            ]
        );

        // Expectations
        $commandSender = $this->commandSender();
        $commandSender->expects('send')->andReturn($result);

        // Execute
        $result = $this->sut->refreshTokens($token, $identifier);

        $this->assertSame(['token' => 'newToken'], $result);
    }

    /**
     * @test
     */
    public function refreshTokensThrowsExceptionWhenResultIsNotOk(): void
    {
        // Setup
        $this->sut = $this->setUpSut();
        $token = ['refresh_token' => 'refresh_token'];
        $identifier = 'username';

        $result = $this->response(false, []);

        // Expectations
        $commandSender = $this->commandSender();
        $commandSender->expects('send')->andReturn($result);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf(RefreshTokenService::MESSAGE_BASE, RefreshTokenService::MESSAGE_RESULT_NOT_OK));

        // Execute
        $this->sut->refreshTokens($token, $identifier);
    }

    /**
     * @test
     */
    public function refreshTokensThrowsExceptionWhenIsValidFlagIsFalse(): void
    {
        // Setup
        $this->sut = $this->setUpSut();
        $token = ['refresh_token' => 'refresh_token'];
        $identifier = 'username';

        $result = $this->response(
            true,
            [
                'flags' => [
                    'isValid' => false,
                ]
            ]
        );

        // Expectations
        $commandSender = $this->commandSender();
        $commandSender->expects('send')->andReturn($result);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf(RefreshTokenService::MESSAGE_BASE, RefreshTokenService::MESSAGE_AUTH_RESULT_NOT_VALID));

        // Execute
        $this->sut->refreshTokens($token, $identifier);
    }

    /**
     * @test
     */
    public function refreshTokensThrowsExceptionWhenIdentityFlagIsMissing(): void
    {
        // Setup
        $this->sut = $this->setUpSut();
        $token = ['refresh_token' => 'refresh_token'];
        $identifier = 'username';

        $result = $this->response(
            true,
            [
                'flags' => [
                    'isValid' => true
                ]
            ]
        );

        // Expectations
        $commandSender = $this->commandSender();
        $commandSender->expects('send')->andReturn($result);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf(RefreshTokenService::MESSAGE_BASE, RefreshTokenService::MESSAGE_IDENTITY_MISSING));

        // Execute
        $this->sut->refreshTokens($token, $identifier);
    }

    /**
     * @return Response|MockInterface
     */
    protected function response(bool $isOk, array $result)
    {
        $instance = m::mock(Response::class);
        $instance->shouldReceive('isOk')
            ->andReturn($isOk);
        $instance->shouldReceive('getResult')
            ->andReturn($result);

        return $instance;
    }

    /**
     * @param Response|null $response
     * @return CommandSender|MockInterface
     */
    protected function commandSender()
    {
        if (!$this->serviceManager->has(CommandSender::class)) {
            $instance = $this->setUpMockService(CommandSender::class);
            $this->serviceManager->setService(CommandSender::class, $instance);
        }

        return $this->serviceManager->get(CommandSender::class);
    }
}
