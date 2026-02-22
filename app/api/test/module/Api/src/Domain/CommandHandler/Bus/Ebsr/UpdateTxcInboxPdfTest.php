<?php

declare(strict_types=1);

/**
 * Update TxcInbox PDF Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\UpdateTxcInboxPdf;
use Dvsa\Olcs\Api\Domain\Repository\TxcInbox as TxcInboxRepo;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\UpdateTxcInboxPdf as Cmd;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox as TxcInboxEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Update TxcInbox PDF Test
 */
class UpdateTxcInboxPdfTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateTxcInboxPdf();
        $this->mockRepo('TxcInbox', TxcInboxRepo::class);
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            DocumentEntity::class => [
                77 => m::mock(DocumentEntity::class)
            ],
        ];

        parent::initReferences();
    }

    /**
     * Tests that the correct method is called for each PDF type
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommand(mixed $pdfType, mixed $method): void
    {
        $id = 99;
        $document = 77;

        $command = Cmd::Create(
            [
                'id' => $id,
                'document' => $document,
                'pdfType' => $pdfType
            ]
        );

        /** @var TxcInboxEntity $txcInbox */
        $txcInbox = m::mock(TxcInboxEntity::class);
        $txcInbox->shouldReceive($method)
            ->once()
            ->andReturnSelf();

        /** @var TxcInboxEntity $txcInbox */
        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getTxcInboxs')
            ->once()
            ->andReturn(new ArrayCollection([$txcInbox]));

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($busReg);

        $this->repoMap['TxcInbox']->shouldReceive('save')
            ->with(m::type(TxcInboxEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * data provider for handleCommand
     */
    public static function handleCommandProvider(): array
    {
        return [
            ['Pdf', 'setPdfDocument'],
            ['pdf', 'setPdfDocument'],
            ['pDF', 'setPdfDocument'],
            ['Route', 'setRouteDocument'],
            ['route', 'setRouteDocument'],
            ['rOUTE', 'setRouteDocument']
        ];
    }
}
