<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\Repository\User as UserRepository;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Command\User\AgreeTerms as Cmd;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\User\AgreeTerms;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

class AgreeTermsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AgreeTerms();
        $this->mockRepo('User', UserRepository::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $userId = 999;

        $command = Cmd::create([]);

        $loggedInUser = m::mock(UserEntity::class);
        $loggedInUser->expects('getId')->andReturn($userId);

        $this->mockedSmServices[AuthorizationService::class]->expects('getIdentity->getUser')
            ->andReturn($loggedInUser);

        $this->mockedSmServices[CacheEncryption::class]->expects('removeCustomItems')
            ->with(CacheEncryption::USER_ACCOUNT_IDENTIFIER, [$userId]);

        $userFromRepo = m::mock(UserEntity::class);
        $userFromRepo->expects('agreeTermsAndConditions')->withNoArgs();

        $this->repoMap['User']->expects('fetchById')->with($userId)->andReturn($userFromRepo);
        $this->repoMap['User']->expects('save')->with($userFromRepo);

        $expectedResult = [
            'id' => [
                'User' => $userId,
            ],
            'messages' => [
                0 => AgreeTerms::SUCCESS_MSG
            ],
        ];

        $this->assertEquals($expectedResult, $this->sut->handleCommand($command)->toArray());
    }
}
