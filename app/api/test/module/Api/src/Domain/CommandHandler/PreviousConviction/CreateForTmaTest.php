<?php

declare(strict_types=1);

/**
 * CreateForTmaTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\PreviousConviction;

use Dvsa\Olcs\Api\Domain\CommandHandler\PreviousConviction\CreateForTma as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\PreviousConviction as PreviousConvictionRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportManagerApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction as PreviousConvictionEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\PreviousConviction\CreateForTma as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * CreateForTmaTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateForTmaTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('PreviousConviction', PreviousConvictionRepo::class);
        $this->mockRepo('TransportManagerApplication', TransportManagerApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(
            [
                'tmaId' => 172,
                'convictionDate' => '2015-06-08',
                'categoryText' => 'CAT-TEXT',
                'notes' => 'NOTES',
                'courtFpn' => 'COURT',
                'penalty' => 'PENALTY',
            ]
        );

        $tma = new TransportManagerApplication();
        $tma->setTransportManager('TRANSPORT_MANAGER');

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchById')->with(172)->once()->andReturn($tma);
        $this->repoMap['PreviousConviction']
            ->shouldReceive('save')->once()->andReturnUsing(
                function (PreviousConvictionEntity $pc) {
                    $pc->setId(852);
                    $this->assertSame('TRANSPORT_MANAGER', $pc->getTransportManager());
                    $this->assertSame('NOTES', $pc->getNotes());
                    $this->assertSame('CAT-TEXT', $pc->getCategoryText());
                    $this->assertSame('COURT', $pc->getCourtFpn());
                    $this->assertSame('PENALTY', $pc->getPenalty());
                    $this->assertEquals(new \DateTime('2015-06-08'), $pc->getConvictionDate());
                }
            );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['previousConviction' => 852], $response->getIds());
        $this->assertSame(['Previous Conviction ID 852 created'], $response->getMessages());
    }
}
