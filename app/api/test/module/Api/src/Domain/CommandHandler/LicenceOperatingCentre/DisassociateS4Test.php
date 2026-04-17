<?php

declare(strict_types=1);

/**
 * DisassociateS4
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceOperatingCentre;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\LicenceOperatingCentre\DisassociateS4 as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceOperatingCentre\DisassociateS4 as CommandHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Domain\Repository;

/**
 * Class DisassociateS4
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceOperatingCentre
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class DisassociateS4Test extends AbstractCommandHandlerTestCase
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
                54 => m::mock(LicenceOperatingCentre::class)->makePartial()
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'licenceOperatingCentres' => [
                $this->references[LicenceOperatingCentre::class][1],
                $this->references[LicenceOperatingCentre::class][54]
            ]
        ];

        $command = Cmd::create($data);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('save')
            ->with($this->references[LicenceOperatingCentre::class][1])->once();
        $this->repoMap['LicenceOperatingCentre']->shouldReceive('save')
            ->with($this->references[LicenceOperatingCentre::class][54])->once();

        $this->sut->handleCommand($command);
    }
}
