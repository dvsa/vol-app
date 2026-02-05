<?php

declare(strict_types=1);

/**
 * Send TM User Created Email Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendTmUserCreated as Sut;
use Dvsa\Olcs\Api\Domain\Command\Email\SendTmUserCreated as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportManagerApplicationRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Send TM User Created Email Test
 */
class SendTmUserCreatedTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('User', UserRepo::class);
        $this->mockRepo('TransportManagerApplication', TransportManagerApplicationRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    public static function dataProviderTestHandleCommand(): array
    {
        return [
            [0, 'application'],
            [1, 'variation']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestHandleCommand')]
    public function testHandleCommand(mixed $isVariation, mixed $uriPart): void
    {
        $userId = 111;
        $tmaId = 222;
        $emailAddress = 'me@test.me';
        $loginId = 'username';

        $command = Cmd::create(
            [
                'user' => $userId,
                'tma' => $tmaId
            ]
        );

        /** @var ContactDetails $contactDetails */
        $contactDetails = m::mock(ContactDetails::class)->makePartial();
        $contactDetails->setEmailAddress($emailAddress);

        /** @var User $user */
        $user = m::mock(User::class)->makePartial();
        $user->setId($userId);
        $user->setLoginId($loginId);
        $user->setContactDetails($contactDetails);

        $this->repoMap['User']->shouldReceive('fetchById')
            ->with($userId)
            ->andReturn($user);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setName('ORGANISATION');

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, m::mock(RefData::class));
        $licence->setLicNo('LIC01');
        $licence->setTranslateToWelsh('N');

        $application = new \Dvsa\Olcs\Api\Entity\Application\Application(
            $licence,
            m::mock(RefData::class),
            $isVariation
        );
        $application->setId(442);

        $tma = new \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication();
        $tma->setApplication($application);
        $tma->setId($tmaId);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchById')->with($tmaId)->once()
            ->andReturn($tma);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')
            ->once()
            ->with(
                m::type(Message::class),
                'transport-manager-user-created',
                [
                    'organisation' => 'ORGANISATION',
                    'reference' => 'LIC01/442',
                    'loginId' => $loginId,
                    'url' => 'http://selfserve/' . $uriPart . '/442/transport-managers/details/' . $tmaId . '/edit-details/'
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
                'Transport Manager user created email sent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
