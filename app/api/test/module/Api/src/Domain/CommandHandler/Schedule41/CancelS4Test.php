<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Schedule41;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Schedule41\CancelS4;
use Dvsa\Olcs\Api\Domain\Repository\S4;
use Dvsa\Olcs\Api\Entity\Application\S4 as S4Entity;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\CancelS4 as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Class CancelS4Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CancelS4Test extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CancelS4();
        $this->mockRepo('S4', S4::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            S4Entity::STATUS_CANCELLED
        ];

        $this->references = [
            S4Entity::class => [
                1 => m::mock(S4Entity::class)->makePartial()
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1,
        ];

        $command = Cmd::create($data);

        $licence = $this->getTestingLicence();
        $licence->addOperatingCentres('OC1');
        $this->references[S4Entity::class][1]->setLicence($licence);

        $this->repoMap['S4']->shouldReceive('save')->once()->with($this->references[S4Entity::class][1]);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\LicenceOperatingCentre\DisassociateS4::class,
            ['licenceOperatingCentres' => $licence->getOperatingCentres()],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['id' => ['s4' => 1], 'messages' => ['S4 Cancelled.']], $result->toArray());
        $this->assertSame(S4Entity::STATUS_CANCELLED, $this->references[S4Entity::class][1]->getOutcome()->getId());
    }
}
