<?php

declare(strict_types=1);

/**
 * Create Goods Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreateVehicleListDocument;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Transfer\Command\Application\CreateVehicleListDocument as Cmd;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVehicleListDocument as LicenceCmd;

/**
 * Create Goods Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateVehicleListDocumentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateVehicleListDocument();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];
        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111
        ];
        $command = Cmd::create($data);

        /** @var DocumentEntity $document */
        $document = m::mock(DocumentEntity::class)->makePartial();

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $data = [
            'id' => 222
        ];
        $result1 = new Result();
        $result1->addId('document', 123);
        $result1->addMessage('Document Created');
        $this->expectedSideEffect(LicenceCmd::class, $data, $result1);

        $this->repoMap['Document']->shouldReceive('fetchById')
            ->with(123)
            ->once()
            ->andReturn($document)
            ->shouldReceive('save')
            ->once()
            ->with($document);

        $result = $this->sut->handleCommand($command);

        $this->assertSame($application, $document->getApplication());

        $expected = [
            'id' => [
                'document' => 123
            ],
            'messages' => [
                'Document Created'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }
}
