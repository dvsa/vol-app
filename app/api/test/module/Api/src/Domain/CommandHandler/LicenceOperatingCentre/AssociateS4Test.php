<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceOperatingCentre;

use Dvsa\Olcs\Api\Entity\Application\S4;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\LicenceOperatingCentre\AssociateS4 as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceOperatingCentre\AssociateS4 as CommandHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Domain\Repository;

/**
 * Class AssociateS4Test
 */
class AssociateS4Test extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LicenceOperatingCentre', Repository\LicenceOperatingCentre::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            LicenceOperatingCentre::class => [
                1 => m::mock(LicenceOperatingCentre::class)->makePartial(),
                54 => m::mock(LicenceOperatingCentre::class)->makePartial(),
            ],
            S4::class => [
                23 => m::mock(S4::class),
                99 => m::mock(S4::class),
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'licenceOperatingCentres' => [1, 54],
            's4' => 23,
        ];

        $command = Cmd::create($data);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('save')
            ->with($this->references[LicenceOperatingCentre::class][1])->once();
        $this->repoMap['LicenceOperatingCentre']->shouldReceive('save')
            ->with($this->references[LicenceOperatingCentre::class][54])->once();

        $this->sut->handleCommand($command);

        $this->assertSame(
            $this->references[S4::class][23],
            $this->references[LicenceOperatingCentre::class][1]->getS4()
        );
        $this->assertSame(
            $this->references[S4::class][23],
            $this->references[LicenceOperatingCentre::class][54]->getS4()
        );
    }
}
