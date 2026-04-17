<?php

declare(strict_types=1);

/**
 * Send Publication Email Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Email\SendPublication as SendPublicationCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendPublication;

/**
 * Send Publication Email Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SendPublicationTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var CommandInterface
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new SendPublication();

        $this->mockRepo('Publication', PublicationRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    /**
     *
     * @param string $isPolice
     * @param int $policeTimes
     * @param int $nonPoliceTimes
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommand(mixed $isPolice, mixed $policeTimes, mixed $nonPoliceTimes, mixed $subject): void
    {
        $publicationId = 1234;
        $filename = 'filename.rtf';
        $documentFilename = '/path/to/' . $filename;
        $documentId = 5678;
        $pubType = 'A&D';
        $publicationNo = 565464;
        $trafficAreaName = 'Scotland';

        $cmdData = [
            'id' => $publicationId,
            'isPolice' => $isPolice
        ];

        $recipients = [
            'foo@bar.com' => 'Recipient 1'
        ];

        $command = SendPublicationCmd::create($cmdData);

        $trafficArea = m::mock(TrafficAreaEntity::class);
        $trafficArea->shouldReceive('getName')->once()->andReturn($trafficAreaName);
        $trafficArea->shouldReceive('getPublicationRecipients')
            ->once()
            ->with($isPolice, $pubType)
            ->andReturn($recipients);

        $document = m::mock(DocumentEntity::class);
        $document->shouldReceive('getFilename')->once()->andReturn($documentFilename);
        $document->shouldReceive('getId')->once()->andReturn($documentId);

        $publication = m::mock(PublicationEntity::class);
        $publication->shouldReceive('getTrafficArea')->once()->andReturn($trafficArea);
        $publication->shouldReceive('getPubType')->once()->andReturn($pubType);
        $publication->shouldReceive('getPublicationNo')->once()->andReturn($publicationNo);
        $publication->shouldReceive('getPoliceDocument')->times($policeTimes)->andReturn($document);
        $publication->shouldReceive('getDocument')->times($nonPoliceTimes)->andReturn($document);

        $this->repoMap['Publication']
            ->shouldReceive('fetchUsingId')
            ->with(m::type(CommandInterface::class))
            ->once()
            ->andReturn($publication);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            SendPublication::EMAIL_TEMPLATE,
            ['filename' => $filename],
            'default'
        );

        $result = new Result();
        $data = [
            'to' => SendPublication::TO_EMAIL,
            'locale' => 'en_GB',
            'subject' => $subject
        ];

        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $this->sut->handleCommand($command);
    }

    /**
     * Data provider for testHandleCommand
     *
     * @return array
     */
    public static function handleCommandProvider(): array
    {
        return [
            ['Y', 1, 0, SendPublication::EMAIL_POLICE_SUBJECT],
            ['N', 0, 1, SendPublication::EMAIL_SUBJECT]
        ];
    }
}
