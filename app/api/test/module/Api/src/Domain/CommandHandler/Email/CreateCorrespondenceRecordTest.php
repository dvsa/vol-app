<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\CreateCorrespondenceRecord as CommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Email\CreateCorrespondenceRecord as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\CorrespondenceInbox as CorrespondenceInboxRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Create Correspondence Record Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateCorrespondenceRecordTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('CorrespondenceInbox', CorrespondenceInboxRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            Licence::class => [
                111 => m::mock(Licence::class)
            ],
            Document::class => [
                222 => m::mock(Document::class)
            ]
        ];

        parent::initReferences();
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('usersProvider')]
    public function testHandleCommand(mixed $email1, mixed $email2, mixed $times): void
    {
        $data = [
            'licence' => 111,
            'document' => 222,
            'type' => 'standard'
        ];

        $orgName = 'organisation name';

        $command = Cmd::create($data);

        /** @var User $user1 */
        $user1 = m::mock(User::class)->makePartial();
        $contactDetails1 = m::mock(ContactDetails::class)->makePartial();
        $contactDetails1->setEmailAddress($email1);
        $user1->setContactDetails($contactDetails1);

        /** @var User $user2 */
        $user2 = m::mock(User::class)->makePartial();
        $contactDetails2 = m::mock(ContactDetails::class)->makePartial();
        $contactDetails2->setEmailAddress($email2);
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
        $organisation->shouldReceive('getName')->times($times)->withNoArgs()->andReturn($orgName);

        $this->references[Licence::class][111]->setOrganisation($organisation);
        $this->references[Licence::class][111]->setTranslateToWelsh(true);
        $this->references[Licence::class][111]->setLicNo('AB12345678');

        $this->repoMap['CorrespondenceInbox']->shouldReceive('save')
            ->with(m::type(CorrespondenceInbox::class))
            ->andReturnUsing(
                function (CorrespondenceInbox $record) {
                    $record->setId(123);
                    $this->assertSame($this->references[Licence::class][111], $record->getLicence());
                    $this->assertSame($this->references[Document::class][222], $record->getDocument());
                }
            );

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')
            ->times($times)
            ->with(
                m::type(Message::class),
                'licensing-information-standard',
                ['licNo' => 'AB12345678', 'operatorName' => $orgName, 'url' => 'http://selfserve/correspondence'],
                'default'
            );

        $result = new Result();

        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => $email1
            ],
            $result
        );

        if ($times === 2) {
            $this->expectedSideEffect(
                SendEmail::class,
                [
                    'to' => $email2
                ],
                $result
            );
        }

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'correspondenceInbox' => 123
            ],
            'messages' => [
                'Correspondence record created',
                'Email sent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function usersProvider(): array
    {
        return [
            ['foo1@bar.com', 'foo2@bar.com', 2],
            ['foo1@bar.com', null, 1]
        ];
    }

    public function testHandleCommandValidationError(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'licence' => 111,
            'document' => 222,
            'type' => 'standard'
        ];
        $command = Cmd::create($data);

        /** @var User $user1 */
        $user1 = m::mock(User::class)->makePartial();
        $contactDetails1 = m::mock(ContactDetails::class)->makePartial();
        $contactDetails1->setEmailAddress(null);
        $user1->setContactDetails($contactDetails1);

        /** @var User $user2 */
        $user2 = m::mock(User::class)->makePartial();
        $contactDetails2 = m::mock(ContactDetails::class)->makePartial();
        $contactDetails2->setEmailAddress('foo@bar@cake');
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

        $this->references[Licence::class][111]->setOrganisation($organisation);
        $this->references[Licence::class][111]->setTranslateToWelsh(true);
        $this->references[Licence::class][111]->setLicNo('AB12345678');

        $this->repoMap['CorrespondenceInbox']->shouldReceive('save')
            ->with(m::type(CorrespondenceInbox::class))
            ->andReturnUsing(
                function (CorrespondenceInbox $record) {
                    $record->setId(123);
                    $this->assertSame($this->references[Licence::class][111], $record->getLicence());
                    $this->assertSame($this->references[Document::class][222], $record->getDocument());
                }
            );

        $this->sut->handleCommand($command);
    }
}
