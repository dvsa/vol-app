<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Rbac\Identity;
use Dvsa\Olcs\Transfer\Command\User\UpdateUserLastLoginAt as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\User\UpdateUserLastLoginAt as Sut;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

class UpdateUserLastLoginAtTest extends AbstractCommandHandlerTestCase
{
    public const USER_ID = 123456;

    /** @var UpdateUserLastLoginAt|m\Mock sut */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('User', User::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    public function testUserLastLoginAtIsUpdatedToCurrentTimestamp(): void
    {
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->withNoArgs()
            ->once()
            ->andReturn($this->getMockIdentity());

        $this->repoMap['User']->shouldReceive('save')
            ->once()
            ->with(m::on(function ($user) {
                if ($user->getId() !== self::USER_ID) {
                    return false;
                }
                if (!$user->getLastLoginAt() instanceof \DateTime) {
                    return false;
                }
                return true;
            }));

        $command = Cmd::create([
            'secureToken' => 'test'
        ]);
        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'user' => self::USER_ID,
            ],
            'messages' => [
                'User last login at updated successfully'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());
    }

    private function getMockIdentity(): m\MockInterface
    {
        /** @var UserEntity $mockUser */
        $mockUser = m::mock(UserEntity::class)->makePartial();
        $mockUser->setId(self::USER_ID);
        $mockUser->setLastLoginAt(null);

        return m::mock(Identity::class)
            ->shouldReceive('getUser')
            ->andReturn($mockUser)
            ->getMock();
    }
}
