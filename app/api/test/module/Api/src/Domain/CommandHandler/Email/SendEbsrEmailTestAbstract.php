<?php

declare(strict_types=1);

/**
 * Send Ebsr Cancelled Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrAbstract;
use Dvsa\Olcs\Email\Data\Message;

/**
 * Send Ebsr Cancelled Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */
abstract class SendEbsrEmailTestAbstract extends AbstractCommandHandlerTestCase
{
    protected $template = null;
    protected $sutClass = null;
    protected const CMD_CLASS = null;

    protected $ebsrSubmissionId = 1234;
    protected $pdfType = null;

    protected $cmdData = [
        'id' => 1234
    ];

    /**
     * @var CommandInterface
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new $this->sutClass();
        $this->mockRepo('EbsrSubmission', EbsrSubmissionRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    /**
     * @param string $orgEmail
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommand(mixed $orgEmail, mixed $adminEmail, mixed $expectedToAddress, mixed $extraCc, mixed $cmdClass): void
    {
        $regNo = 5678;
        $laDescription1 = 'la description 1';
        $laEmail1 = 'terry.valtech+la1@gmail.com';
        $laEmail1User1 = 'terry.valtech+la1user1@gmail.com';
        $laEmail1User2 = 'terry.valtech+la1user2@gmail.com';
        $laEmail2 = 'terry.valtech+la2@gmail.com';
        $expectedCcAddresses = array_merge([$laEmail1, $laEmail1User1, $laEmail1User2, $laEmail2], $extraCc);
        $laDescription2 = 'la description 2';
        $startPoint = 'start point';
        $endPoint = 'end point';
        $serviceNumbers = '99999 (12345,567910)';
        $orgAdminEmails = [0 => $adminEmail];
        $submissionResult = [];

        $submittedDate = '2015-01-15';
        $formattedSubmittedDate = date(SendEbsrAbstract::DATE_FORMAT, strtotime($submittedDate));

        $effectiveDate = new \DateTime('2015-01-16 00:00:00');
        $formattedEffectiveDate = $effectiveDate->format(SendEbsrAbstract::DATE_FORMAT);

        $contactDetails1 = m::mock(ContactDetailsEntity::class);
        $contactDetails1->shouldReceive('getEmailAddress')->once()->withNoArgs()->andReturn($laEmail1User1);

        $contactDetails2 = m::mock(ContactDetailsEntity::class);
        $contactDetails2->shouldReceive('getEmailAddress')->once()->withNoArgs()->andReturn($laEmail1User2);

        $laUser1 = m::mock(UserEntity::class);
        $laUser1->shouldReceive('getContactDetails')->once()->withNoArgs()->andReturn($contactDetails1);

        $laUser2 = m::mock(UserEntity::class);
        $laUser2->shouldReceive('getContactDetails')->once()->withNoArgs()->andReturn($contactDetails2);

        //tests missing contact details are safely ignored
        $laUser3 = m::mock(UserEntity::class);
        $laUser3->shouldReceive('getContactDetails')->once()->withNoArgs()->andReturnNull();

        $la1Users = new ArrayCollection([$laUser1, $laUser2, $laUser3]);

        $la1 = m::mock(LocalAuthorityEntity::class);
        $la1->shouldReceive('getDescription')->once()->withNoArgs()->andReturn($laDescription1);
        $la1->shouldReceive('getEmailAddress')->once()->withNoArgs()->andReturn($laEmail1);
        $la1->shouldReceive('getUsers')->once()->withNoArgs()->andReturn($la1Users);

        $la2 = m::mock(LocalAuthorityEntity::class);
        $la2->shouldReceive('getDescription')->once()->withNoArgs()->andReturn($laDescription2);
        $la2->shouldReceive('getEmailAddress')->once()->withNoArgs()->andReturn($laEmail2);
        $la2->shouldReceive('getUsers')->once()->withNoArgs()->andReturn(new ArrayCollection());

        $la = new ArrayCollection([$la1, $la2]);

        $command = $cmdClass::create($this->cmdData);

        $busRegEntity = m::mock(BusRegEntity::class);
        $busRegEntity->shouldReceive('getRegNo')->times(2)->withNoArgs()->andReturn($regNo);
        $busRegEntity->shouldReceive('getStartPoint')->once()->withNoArgs()->andReturn($startPoint);
        $busRegEntity->shouldReceive('getFinishPoint')->once()->withNoArgs()->andReturn($endPoint);
        $busRegEntity->shouldReceive('getEffectiveDate')->once()->withNoArgs()->andReturn($effectiveDate);
        $busRegEntity->shouldReceive('getLocalAuthoritys')->times(2)->withNoArgs()->andReturn($la);
        $busRegEntity->shouldReceive('getFormattedServiceNumbers')->once()->withNoArgs()->andReturn($serviceNumbers);
        $busRegEntity->shouldReceive('getPublicationSectionForGrantEmail')->never(); //only for registered & cancelled
        $busRegEntity->shouldReceive('getPublicationLinksForGrantEmail')->never(); //only for registered & cancelled

        $ebsrSubmissionEntity = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmissionEntity->shouldReceive('getId')->withNoArgs()->andReturn($this->ebsrSubmissionId);
        $ebsrSubmissionEntity->shouldReceive('getSubmittedDate')->withNoArgs()->andReturn($submittedDate);
        $ebsrSubmissionEntity->shouldReceive('getOrganisationEmailAddress')->once()->withNoArgs()->andReturn($orgEmail);
        $ebsrSubmissionEntity->shouldReceive('getBusReg')->once()->withNoArgs()->andReturn($busRegEntity);
        $ebsrSubmissionEntity->shouldReceive('getOrganisation->getAdminEmailAddresses')
            ->withNoArgs()
            ->andReturn($orgAdminEmails);
        $ebsrSubmissionEntity->shouldReceive('getDecodedSubmissionResult')->withNoArgs()->andReturn($submissionResult);

        $this->repoMap['EbsrSubmission']
            ->shouldReceive('fetchUsingId')
            ->with(m::type(CommandInterface::class), Query::HYDRATE_OBJECT, null)
            ->once()
            ->andReturn($ebsrSubmissionEntity);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            $this->template,
            [
                'submissionDate' => $formattedSubmittedDate,
                'registrationNumber' => $regNo,
                'origin' => $startPoint,
                'destination' => $endPoint,
                'lineName' => $serviceNumbers,
                'startDate' => $formattedEffectiveDate,
                'localAuthoritys' => $laDescription1 . ', ' . $laDescription2,
                'submissionErrors' => $submissionResult,
                'hasBusData' => true,
                'publicationId' => null,
                'pdfType' => $this->pdfType
            ],
            'default'
        );

        $result = new Result();
        $data = [
            'to' => $expectedToAddress,
            'locale' => 'en_GB',
            'subject' => 'email.' . $this->template . '.subject'
        ];

        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['ebsrSubmission' => $this->ebsrSubmissionId], $result->getIds());
        $this->assertSame(['Email sent'], $result->getMessages());

        /** @var Message $message */
        $message = $this->sut->getMessage();
        $this->assertSame($expectedCcAddresses, $message->getCc());
        $this->assertSame('email.' . $this->template . '.subject', $message->getSubject());
    }

    public static function handleCommandProvider(): array
    {
        return [
            ['test@test.com', 'foo@bar.com', 'test@test.com', ['foo@bar.com'], static::CMD_CLASS],
            ['',  'foo@bar.com', 'foo@bar.com', [], static::CMD_CLASS]
        ];
    }
}
