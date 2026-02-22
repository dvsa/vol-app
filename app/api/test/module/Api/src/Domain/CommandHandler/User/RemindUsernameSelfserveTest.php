<?php

declare(strict_types=1);

/**
 * Remind Username Selfserve Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\Command\Email\SendUsernameMultiple as SendUsernameMultipleCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUsernameSingle as SendUsernameSingleCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\User\RemindUsernameSelfserve as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\User\RemindUsernameSelfserve as Sut;
use Mockery as m;

/**
 * Remind Username Selfserve Test
 */
class RemindUsernameSelfserveTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('User', UserRepo::class);

        parent::setUp();
    }

    public function testHandleCommandSingle(): void
    {
        $data = [
            'licenceNumber' => 'AB12345678',
            'emailAddress' => 'test@test.me'
        ];

        /** @var User $user1 */
        $user1Id = 1;
        $user1 = m::mock(User::class)->makePartial();
        $user1->setId($user1Id);

        $users = [$user1];

        $this->repoMap['User']
            ->shouldReceive('fetchForRemindUsername')
            ->once()
            ->with($data['licenceNumber'], $data['emailAddress'])
            ->andReturn($users);

        $command = Cmd::create($data);

        $this->expectedSideEffect(
            SendUsernameSingleCmd::class,
            [
                'user' => $user1Id,
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'USERNAME_REMINDER_SENT_SINGLE'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandMultiple(): void
    {
        $data = [
            'licenceNumber' => 'AB12345678',
            'emailAddress' => 'test@test.me'
        ];

        /** @var User $user1 */
        $user1 = m::mock(User::class)->makePartial();
        $user1->setId(1);

        /** @var User $user2 */
        $user2 = m::mock(User::class)->makePartial();
        $user2->setId(2);

        $users = [$user1, $user2];

        $this->repoMap['User']
            ->shouldReceive('fetchForRemindUsername')
            ->once()
            ->with($data['licenceNumber'], $data['emailAddress'])
            ->andReturn($users);

        $command = Cmd::create($data);

        $this->expectedSideEffect(
            SendUsernameMultipleCmd::class,
            [
                'licenceNumber' => $data['licenceNumber']
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'USERNAME_REMINDER_SENT_MULTIPLE'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandThrowsNotFoundException(): void
    {
        $data = [
            'licenceNumber' => 'AB12345678',
            'emailAddress' => 'test@test.me'
        ];

        $users = [];

        $command = Cmd::create($data);

        $this->repoMap['User']
            ->shouldReceive('fetchForRemindUsername')
            ->once()
            ->with($data['licenceNumber'], $data['emailAddress'])
            ->andReturn($users);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'ERR_USERNAME_NOT_FOUND'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
