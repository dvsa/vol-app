<?php

declare(strict_types=1);

/**
 * Abstract for testing ebsr registered and cancelled emails
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
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrAbstract;

/**
 * Abstract for testing ebsr registered and cancelled emails
 */
abstract class SendEbsrRegCancelEmailTestAbstract extends AbstractCommandHandlerTestCase
{
    protected $template = null;
    protected $sutClass = null;
    protected const CMD_CLASS = null;

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

        $this->references = [
            PublicationSectionEntity::class => [
                26 => m::mock(PublicationSectionEntity::class)
            ],
        ];

        parent::setUp();
    }

    /**
     * @param string $cmdClass
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommand(mixed $cmdClass): void
    {
        $ebsrSubmissionId = 1234;
        $regNo = 5678;
        $startPoint = 'start point';
        $endPoint = 'end point';
        $serviceNumbers = '99999 (12345,567910)';
        $orgEmail = 'foo@bar.com';
        $publicationInfo = 'publicationInfo';
        $submissionResult = [];
        $orgAdminEmails = [0 => 'terry.valtech@gmail.com'];

        $submittedDate = '2015-01-15';
        $formattedSubmittedDate = date(SendEbsrAbstract::DATE_FORMAT, strtotime($submittedDate));

        $effectiveDate = new \DateTime('2015-01-16 00:00:00');
        $formattedEffectiveDate = $effectiveDate->format(SendEbsrAbstract::DATE_FORMAT);

        $command = $cmdClass::create(['id' => $ebsrSubmissionId]);

        $busRegEntity = m::mock(BusRegEntity::class);
        $busRegEntity->shouldReceive('getRegNo')->times(2)->andReturn($regNo);
        $busRegEntity->shouldReceive('getStartPoint')->once()->andReturn($startPoint);
        $busRegEntity->shouldReceive('getFinishPoint')->once()->andReturn($endPoint);
        $busRegEntity->shouldReceive('getEffectiveDate')->once()->andReturn($effectiveDate);
        $busRegEntity->shouldReceive('getLocalAuthoritys')->times(2)->andReturn(new ArrayCollection());
        $busRegEntity->shouldReceive('getFormattedServiceNumbers')->once()->andReturn($serviceNumbers);
        $busRegEntity->shouldReceive('getPublicationSectionForGrantEmail')->once()->andReturn(26);
        $busRegEntity->shouldReceive('getPublicationLinksForGrantEmail')->once()->andReturn($publicationInfo);

        $ebsrSubmissionEntity = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmissionEntity->shouldReceive('getId')->andReturn($ebsrSubmissionId);
        $ebsrSubmissionEntity->shouldReceive('getSubmittedDate')->andReturn($submittedDate);
        $ebsrSubmissionEntity->shouldReceive('getOrganisationEmailAddress')->once()->andReturn($orgEmail);
        $ebsrSubmissionEntity->shouldReceive('getBusReg')->once()->andReturn($busRegEntity);
        $ebsrSubmissionEntity->shouldReceive('getDecodedSubmissionResult')->andReturn($submissionResult);
        $ebsrSubmissionEntity->shouldReceive('getOrganisation->getAdminEmailAddresses')->andReturn($orgAdminEmails);

        $this->repoMap['EbsrSubmission']
            ->shouldReceive('fetchUsingId')
            ->with(m::type($cmdClass), Query::HYDRATE_OBJECT, null)
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
                'localAuthoritys' => '',
                'submissionErrors' => $submissionResult,
                'hasBusData' => true,
                'publicationId' => $publicationInfo,
                'pdfType' => null
            ],
            'default'
        );

        $result = new Result();
        $data = [
            'to' => $orgEmail,
            'locale' => 'en_GB',
            'subject' => 'email.' . $this->template . '.subject'
        ];

        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['ebsrSubmission' => $ebsrSubmissionId], $result->getIds());
        $this->assertSame(['Email sent'], $result->getMessages());
    }

    public static function handleCommandProvider(): array
    {
        return [
            [static::CMD_CLASS],
            [static::CMD_CLASS]
        ];
    }
}
