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
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink as RetrievalLinkEntity;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Service\Retrieval\RetrievalLinkCreator;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;

/**
 * Send Publication Email Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class SendPublicationTest extends AbstractCommandHandlerTestCase
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
            RetrievalLinkCreator::class => m::mock(RetrievalLinkCreator::class),
            ToggleService::class => m::mock(ToggleService::class),
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

        // Toggle off: legacy attachment path (document is attached, no retrieval link).
        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->with(FeatureToggle::RETRIEVE_VIA_LINK)
            ->andReturn(false);

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
     * Toggle on + non-police publication: delivered via a secure retrieval link (no attachment).
     */
    public function testHandleCommandDeliversViaRetrievalLinkWhenEnabled(): void
    {
        $publicationId = 1234;
        $filename = 'filename.rtf';
        $documentId = 5678;
        $pubType = 'A&D';
        $token = 'opaque-token';

        $command = SendPublicationCmd::create(['id' => $publicationId, 'isPolice' => 'N']);

        $trafficArea = m::mock(TrafficAreaEntity::class);
        $trafficArea->shouldReceive('getName')->once()->andReturn('Scotland');
        $trafficArea->shouldReceive('getPublicationRecipients')->once()
            ->with('N', $pubType)->andReturn(['foo@bar.com' => 'Recipient 1']);

        $document = m::mock(DocumentEntity::class);
        $document->shouldReceive('getFilename')->once()->andReturn('/path/to/' . $filename);
        $document->shouldReceive('getId')->once()->andReturn($documentId);

        $publication = m::mock(PublicationEntity::class);
        $publication->shouldReceive('getTrafficArea')->once()->andReturn($trafficArea);
        $publication->shouldReceive('getPubType')->once()->andReturn($pubType);
        $publication->shouldReceive('getPublicationNo')->once()->andReturn(565464);
        $publication->shouldReceive('getDocument')->once()->andReturn($document);
        $publication->shouldReceive('getId')->andReturn($publicationId);

        $this->repoMap['Publication']->shouldReceive('fetchUsingId')
            ->with(m::type(CommandInterface::class))->once()->andReturn($publication);

        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')->with(FeatureToggle::RETRIEVE_VIA_LINK)->andReturn(true);

        $link = m::mock(RetrievalLinkEntity::class);
        $link->shouldReceive('getToken')->once()->andReturn($token);

        $this->mockedSmServices[RetrievalLinkCreator::class]->shouldReceive('create')
            ->with([$documentId], null, 'publication', 'publication:' . $publicationId)
            ->once()
            ->andReturn($link);

        // Template data now carries the link; no document is attached.
        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            SendPublication::EMAIL_TEMPLATE,
            ['filename' => $filename, 'retrievalLink' => 'http://selfserve/retrieve/' . $token],
            'default'
        );

        $this->expectedSideEffect(
            SendEmail::class,
            ['to' => SendPublication::TO_EMAIL, 'locale' => 'en_GB', 'subject' => SendPublication::EMAIL_SUBJECT],
            new Result()
        );

        $this->sut->handleCommand($command);
    }

    /**
     * Toggle on + police publication: one OTP-gated link per recipient, each bound to that
     * recipient's address, and one email each.
     */
    public function testHandleCommandDeliversPoliceViaPerRecipientOtpLinks(): void
    {
        $publicationId = 1234;
        $filename = 'police.rtf';
        $documentId = 5678;
        $pubType = 'A&D';

        $command = SendPublicationCmd::create(['id' => $publicationId, 'isPolice' => 'Y']);

        $recipients = ['a@police.example' => 'Force A', 'b@police.example' => 'Force B'];

        $trafficArea = m::mock(TrafficAreaEntity::class);
        $trafficArea->shouldReceive('getName')->once()->andReturn('Scotland');
        $trafficArea->shouldReceive('getPublicationRecipients')->once()->with('Y', $pubType)->andReturn($recipients);

        $document = m::mock(DocumentEntity::class);
        $document->shouldReceive('getFilename')->once()->andReturn('/path/to/' . $filename);
        $document->shouldReceive('getId')->times(2)->andReturn($documentId);

        $publication = m::mock(PublicationEntity::class);
        $publication->shouldReceive('getTrafficArea')->once()->andReturn($trafficArea);
        $publication->shouldReceive('getPubType')->once()->andReturn($pubType);
        $publication->shouldReceive('getPublicationNo')->once()->andReturn(565464);
        $publication->shouldReceive('getPoliceDocument')->once()->andReturn($document);
        $publication->shouldReceive('getId')->andReturn($publicationId);

        $this->repoMap['Publication']->shouldReceive('fetchUsingId')
            ->with(m::type(CommandInterface::class))->once()->andReturn($publication);

        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')->with(FeatureToggle::RETRIEVE_VIA_LINK)->andReturn(true);

        $linkA = m::mock(RetrievalLinkEntity::class);
        $linkA->shouldReceive('getToken')->once()->andReturn('token-a');
        $linkB = m::mock(RetrievalLinkEntity::class);
        $linkB->shouldReceive('getToken')->once()->andReturn('token-b');

        // Each recipient gets their OWN link, bound to their address, under the police (otp) flow.
        $this->mockedSmServices[RetrievalLinkCreator::class]->shouldReceive('create')
            ->with([$documentId], 'a@police.example', 'publication-police', 'publication:' . $publicationId)
            ->once()->andReturn($linkA);
        $this->mockedSmServices[RetrievalLinkCreator::class]->shouldReceive('create')
            ->with([$documentId], 'b@police.example', 'publication-police', 'publication:' . $publicationId)
            ->once()->andReturn($linkB);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            SendPublication::EMAIL_TEMPLATE,
            ['filename' => $filename, 'retrievalLink' => 'http://selfserve/retrieve/token-a'],
            'default'
        )->once();
        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            SendPublication::EMAIL_TEMPLATE,
            ['filename' => $filename, 'retrievalLink' => 'http://selfserve/retrieve/token-b'],
            'default'
        )->once();

        $this->expectedSideEffect(
            SendEmail::class,
            ['to' => SendPublication::TO_EMAIL, 'subject' => SendPublication::EMAIL_POLICE_SUBJECT],
            new Result(),
            2
        );

        $this->sut->handleCommand($command);
    }

    /**
     * Data provider for testHandleCommand
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function handleCommandProvider(): \Iterator
    {
        yield ['Y', 1, 0, SendPublication::EMAIL_POLICE_SUBJECT];
        yield ['N', 0, 1, SendPublication::EMAIL_SUBJECT];
    }
}
