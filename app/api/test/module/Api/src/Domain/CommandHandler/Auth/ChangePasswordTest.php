<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\ChangePassword;
use Dvsa\Olcs\Auth\Exception\ChangePasswordException;
use Dvsa\Olcs\Transfer\Command\Auth\ChangePassword as ChangePasswordCmd;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Result\Auth\ChangePasswordResult;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\Http\Response;
use Mockery as m;
use LmcRbacMvc\Identity\IdentityInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see ChangePassword
 */
class ChangePasswordTest extends AbstractCommandHandlerTestCase
{
    private m\MockInterface $adapter;
    private string $username = 'username';
    private string $oldPassword = 'oldPassword';
    private string $newPassword = 'newPassword';
    private string $realm = 'realm';
    private ChangePasswordCmd $command;

    public function setUp(): void
    {
        $this->adapter = m::mock(ValidatableAdapterInterface::class);

        $this->mockedSmServices = [
            ValidatableAdapterInterface::class => $this->adapter,
            AuthorizationService::class => $this->setUpAuthService(),
        ];

        $this->command = $this->getCommand();
        $this->sut = new ChangePassword($this->adapter);

        parent::setUp();
    }

    public function setUpAuthService(): m\MockInterface
    {
        $mockUser = m::mock(User::class);
        $mockUser->expects('getLoginId')->withNoArgs()->andReturn($this->username);

        $identity = m::mock(IdentityInterface::class);
        $identity->expects('getUser')->withNoArgs()->andReturn($mockUser);

        $authService = m::mock(AuthorizationService::class);
        $authService->expects('getIdentity')->withNoArgs()->andReturn($identity);

        return $authService;
    }

    public function testHandleCommandSuccess(): void
    {
        $this->adapterResult(new ChangePasswordResult(ChangePasswordResult::SUCCESS));

        $result = $this->sut->handleCommand($this->command);
        $this->assertEquals(ChangePasswordResult::SUCCESS, $result->getFlag('code'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpChangeResult')]
    public function testHandleCommandFail(ChangePasswordResult $changeResult): void
    {
        $this->adapterResult($changeResult);

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals($changeResult->getCode(), $result->getFlag('code'));
        $this->assertEquals($changeResult->getMessage(), $result->getFlag('message'));
    }

    public static function dpChangeResult(): array
    {
        return [
            'Generic failure' => [
                new ChangePasswordResult(ChangePasswordResult::FAILURE, 'generic failure'),
            ],
            'Password invalid' => [
                new ChangePasswordResult(ChangePasswordResult::FAILURE_NEW_PASSWORD_INVALID, 'invalid password failure'),
            ],
            'Client error' => [
                new ChangePasswordResult(ChangePasswordResult::FAILURE_CLIENT_ERROR, 'client failure')
            ],
            'Not authorised' => [
                new ChangePasswordResult(ChangePasswordResult::FAILURE_NOT_AUTHORIZED, 'not authorised failure')
            ]
        ];
    }

    private function adapterResult(mixed $changeResult): void
    {
        $this->adapter->expects('changePassword')
            ->with($this->username, $this->oldPassword, $this->newPassword)
            ->andReturn($changeResult);
    }

    private function getCommand(): ChangePasswordCmd
    {
        $cmdData = [
            'username' => $this->username,
            'password' => $this->oldPassword,
            'newPassword' => $this->newPassword
        ];

        return ChangePasswordCmd::create($cmdData);
    }
}
