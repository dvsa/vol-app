<?php

declare(strict_types=1);

/**
 * Send Username Multiple Email Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendUsernameMultiple as Sut;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUsernameMultiple as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Send Username Multiple Email Test
 */
class SendUsernameMultipleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('Licence', LicenceRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $licNo = 'AB12345678';

        $data = [
            'licenceNumber' => $licNo
        ];
        $command = Cmd::create($data);

        /** @var User $user1 */
        $user1 = m::mock(User::class)->makePartial();
        $user1->setId(1);
        $user1->setLoginId('u1');
        $contactDetails1 = m::mock(ContactDetails::class)->makePartial();
        $contactDetails1->setEmailAddress('u1@bar.com');
        $user1->setContactDetails($contactDetails1);

        /** @var User $user2 */
        $user2 = m::mock(User::class)->makePartial();
        $user2->setId(2);
        $user2->setLoginId('u2');
        $contactDetails2 = m::mock(ContactDetails::class)->makePartial();
        $contactDetails2->setEmailAddress('u2@bar.com');
        $user2->setContactDetails($contactDetails2);

        /** @var OrganisationUser $orgUser1 */
        $orgUser1 = m::mock(OrganisationUser::class)->makePartial();
        $orgUser1->setUser($user1);
        /** @var OrganisationUser $orgUser2 */
        $orgUser2 = m::mock(OrganisationUser::class)->makePartial();
        $orgUser2->setUser($user2);

        /** @var Organisation $organisation */
        $organisation = m::mock(Organisation::class)->makePartial();
        $organisation->shouldReceive('getAdminOrganisationUsers')
            ->andReturn([$orgUser1, $orgUser2]);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setLicNo($licNo);
        $licence->setTranslateToWelsh(true);
        $licence->setOrganisation($organisation);

        $this->repoMap['Licence']->shouldReceive('fetchByLicNo')
            ->with($licNo)
            ->andReturn($licence);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')
            ->once()
            ->with(
                m::type(Message::class),
                'user-forgot-username-multiple',
                [
                    'loginId' => 'u1',
                    'url' => 'http://selfserve/'
                ],
                'default'
            );

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')
            ->once()
            ->with(
                m::type(Message::class),
                'user-forgot-username-multiple',
                [
                    'loginId' => 'u2',
                    'url' => 'http://selfserve/'
                ],
                'default'
            );

        $result = new Result();
        $data = [
            'to' => 'u1@bar.com'
        ];
        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $result = new Result();
        $data = [
            'to' => 'u2@bar.com'
        ];
        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => [1, 2]
            ],
            'messages' => [
                'Username reminder email sent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
