<?php

declare(strict_types=1);

/**
 * Send User Created Email Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendUserCreated as Sut;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUserCreated as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser as OrganisationUserEntity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Send User Created Email Test
 */
class SendUserCreatedTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('User', UserRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    public function testHandleCommandForSelfserveUser(): void
    {
        $userId = 111;
        $emailAddress = 'me@test.me';
        $loginId = 'username';
        $orgName = 'org name';

        $command = Cmd::create(
            [
                'user' => $userId,
            ]
        );

        /** @var ContactDetails $contactDetails */
        $contactDetails = m::mock(ContactDetails::class)->makePartial();
        $contactDetails->setEmailAddress($emailAddress);

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(1000);
        $organisation->setName($orgName);

        /** @var OrganisationUserEntity $organisation */
        $organisationUser = m::mock(OrganisationUserEntity::class)->makePartial();
        $organisationUser->setOrganisation($organisation);

        /** @var User $user */
        $user = new User('', User::USER_TYPE_OPERATOR);
        $user->setId($userId);
        $user->setLoginId($loginId);
        $user->setContactDetails($contactDetails);
        $user->getOrganisationUsers()->add($organisationUser);

        $this->repoMap['User']->shouldReceive('fetchById')
            ->with($userId)
            ->andReturn($user);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')
            ->once()
            ->with(
                m::type(Message::class),
                'user-created',
                [
                    'orgName' => $orgName,
                    'loginId' => $loginId,
                    'url' => 'http://selfserve/'
                ],
                'default'
            );

        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => $emailAddress
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId
            ],
            'messages' => [
                'User created email sent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandForInternalUser(): void
    {
        $userId = 111;
        $emailAddress = 'me@test.me';
        $loginId = 'username';

        $command = Cmd::create(
            [
                'user' => $userId,
            ]
        );

        /** @var ContactDetails $contactDetails */
        $contactDetails = m::mock(ContactDetails::class)->makePartial();
        $contactDetails->setEmailAddress($emailAddress);

        /** @var User $user */
        $user = new User('', User::USER_TYPE_INTERNAL);
        $user->setId($userId);
        $user->setLoginId($loginId);
        $user->setContactDetails($contactDetails);

        $this->repoMap['User']->shouldReceive('fetchById')
            ->with($userId)
            ->andReturn($user);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')
            ->once()
            ->with(
                m::type(Message::class),
                'user-created',
                [
                    'orgName' => 'DVSA',
                    'loginId' => $loginId,
                    'url' => 'http://internal/'
                ],
                'default'
            );

        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => $emailAddress
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId
            ],
            'messages' => [
                'User created email sent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
