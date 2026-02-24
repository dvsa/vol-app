<?php

declare(strict_types=1);

/**
 * Send Username Single Email Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendUsernameSingle as Sut;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUsernameSingle as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Send Username Single Email Test
 */
class SendUsernameSingleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('User', UserRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $userId = 111;
        $licNo = 'AB12345678';
        $emailAddress = 'me@test.me';
        $loginId = 'username';

        $data = [
            'user' => $userId,
            'licenceNumber' => $licNo
        ];
        $command = Cmd::create($data);

        /** @var User $user */
        $user = m::mock(User::class)->makePartial();
        $user->setId($userId);
        $user->setLoginId($loginId);
        $contactDetails = m::mock(ContactDetails::class)->makePartial();
        $contactDetails->setEmailAddress($emailAddress);
        $user->setContactDetails($contactDetails);

        $this->repoMap['User']->shouldReceive('fetchById')
            ->with($userId)
            ->andReturn($user);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setLicNo($licNo);
        $licence->setTranslateToWelsh(true);

        $this->repoMap['Licence']->shouldReceive('fetchByLicNo')
            ->with($licNo)
            ->andReturn($licence);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')
            ->once()
            ->with(
                m::type(Message::class),
                'user-forgot-username-single',
                [
                    'loginId' => $loginId,
                    'url' => 'http://selfserve/'
                ],
                'default'
            );

        $result = new Result();
        $data = [
            'to' => $emailAddress
        ];
        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId
            ],
            'messages' => [
                'Username reminder email sent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
