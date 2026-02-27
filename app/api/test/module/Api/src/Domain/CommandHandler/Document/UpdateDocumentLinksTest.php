<?php

declare(strict_types=1);

/**
 * Update Document Links Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Repository\Document;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\UpdateDocumentLinks;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentLinks as Cmd;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity;

/**
 * Update Document Links Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateDocumentLinksTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateDocumentLinks();
        $this->mockRepo('Document', Document::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [
            Entity\Application\Application::class => [
                123 => m::mock(Entity\Application\Application::class)
            ],
            Entity\Surrender::class => [
                777 => m::mock(Entity\Surrender::class),
            ]
        ];
        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111,
            'application' => 123,
            'surrender' => 777,
            'conversation' => null,
        ];

        $command = Cmd::create($data);

        /** @var Entity\Doc\Document $document */
        $document = m::mock(Entity\Doc\Document::class)->makePartial();

        $this->repoMap['Document']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($document)
            ->shouldReceive('save')
            ->once()
            ->with($document);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Document linked to Application: 123',
                'Document linked to Surrender: 777'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->references[Entity\Application\Application::class][123], $document->getApplication());
    }
}
