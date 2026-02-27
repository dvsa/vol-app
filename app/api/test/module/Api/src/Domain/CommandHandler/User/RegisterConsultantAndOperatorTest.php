<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\CommandHandler\User\RegisterConsultantAndOperator;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Entity\User\Role;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Command\User\RegisterConsultantAndOperator as RegisterConsultantAndOperatorCommand;
use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve as RegisterUserSelfServeCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;

class RegisterConsultantAndOperatorTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RegisterConsultantAndOperator();
        $this->mockRepo('User', UserRepo::class);
        $this->mockRepo('Role', \Dvsa\Olcs\Api\Domain\Repository\Role::class);

        $mockAuthService = m::mock(\LmcRbacMvc\Service\AuthorizationService::class);
        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class] = $mockAuthService;

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $operatorDetails = ['organisationName' => 'Operator Org'];
        $operatorModifiedDetails = ['organisationName' => 'Operator Org', 'createdByConsultant' => true];

        $command = RegisterConsultantAndOperatorCommand::create(
            [
                'operatorDetails' => $operatorDetails,
                'consultantDetails' => []
            ]
        );

        $operatorResult = new Result();
        $operatorResult->addId('user', 100)->addMessage('User created successfully');

        $this->expectedSideEffect(
            RegisterUserSelfServeCommand::class,
            $operatorModifiedDetails,
            $operatorResult
        );

        $organisationId = 200;
        $organisation = m::mock();
        $organisation->shouldReceive('getId')->andReturn($organisationId);

        $organisationUser = m::mock();
        $organisationUser->shouldReceive('getOrganisation')->andReturn($organisation);

        $user = m::mock(UserEntity::class)->makePartial();
        $user->shouldReceive('getOrganisationUsers->first')->andReturn($organisationUser);

        $this->repoMap['User']->shouldReceive('fetchById')->with(100)->andReturn($user);

        $consultantDetails['organisation'] = $organisationId;

        $consultantResult = new Result();
        $consultantResult->addId('user', 101)->addMessage('User created successfully');

        $consultant = m::mock(UserEntity::class)->makePartial();
        $this->repoMap['User']->shouldReceive('fetchById')->with(101)->andReturn($consultant);

        $consultant->shouldReceive('setRoles')
            ->with(m::type(\Doctrine\Common\Collections\ArrayCollection::class))
            ->once()
            ->andReturnSelf();

        $consultant->expects('agreeTermsAndConditions')->withNoArgs();

        $this->repoMap['User']->shouldReceive('save')->with($consultant);

        $mockRole = m::mock(Role::class);
        $this->repoMap['Role']->shouldReceive('fetchByRole')->with(Role::ROLE_OPERATOR_TC)->andReturn($mockRole);

        $this->expectedSideEffect(
            RegisterUserSelfServeCommand::class,
            $consultantDetails,
            $consultantResult
        );

        $result = $this->sut->handleCommand($command);
        $this->assertEquals(['User created successfully', 'User created successfully'], $result->getMessages());
    }
}
