<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\Application\Grant\ProcessApplicationOperatingCentres as ProcessAocCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant\ProcessApplicationOperatingCentres;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Process Application Operating Centres Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ProcessApplicationOperatingCentresTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ProcessApplicationOperatingCentres();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('LicenceOperatingCentre', \Dvsa\Olcs\Api\Domain\Repository\LicenceOperatingCentre::class);
        $this->mockRepo(
            'ApplicationOperatingCentre',
            \Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre::class
        );

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandAdd(): void
    {
        $data = [
            'id' => 111
        ];

        $command = ProcessAocCmd::create($data);

        $oc = m::mock(OperatingCentre::class)->makePartial();

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('A');
        $aoc->setIsInterim(true);
        $aoc->setNoOfVehiclesRequired(10);
        $aoc->setOperatingCentre($oc);

        $aocs = [
            $aoc
        ];

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('addOperatingCentres')->with(m::type(LicenceOperatingCentre::class))->once();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('save')
            ->with($aoc);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (LicenceOperatingCentre $loc) use ($oc) {
                    $this->assertEquals(10, $loc->getNoOfVehiclesRequired());
                    $this->assertSame($oc, $loc->getOperatingCentre());
                    $this->assertNull($loc->getS4());
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 licence operating centre(s) created',
                '0 licence operating centre(s) updated',
                '0 licence operating centre(s) removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertFalse($aoc->getIsInterim());
    }

    public function testHandleCommandUpdate(): void
    {
        $data = [
            'id' => 111
        ];

        $command = ProcessAocCmd::create($data);

        $oc = m::mock(OperatingCentre::class)->makePartial();
        $application = m::mock(ApplicationEntity::class)->makePartial();

        $aoc = new ApplicationOperatingCentre($application, $oc);
        $aoc->setAction('U');
        $aoc->setIsInterim(true);
        $aoc->setNoOfVehiclesRequired(10);

        $aocs = [
            $aoc
        ];

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        $loc = new LicenceOperatingCentre($licence, $oc);

        /** @var ApplicationEntity $application */
        $application->setLicence($licence);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('save')
            ->with($aoc)
            ->shouldReceive('findCorrespondingLoc')
            ->once()
            ->with($aoc, $licence)
            ->andReturn($loc);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (LicenceOperatingCentre $loc) use ($oc) {
                    $this->assertEquals(10, $loc->getNoOfVehiclesRequired());
                    $this->assertSame($oc, $loc->getOperatingCentre());
                    $this->assertNull($loc->getS4());
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '0 licence operating centre(s) created',
                '1 licence operating centre(s) updated',
                '0 licence operating centre(s) removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertFalse($aoc->getIsInterim());
    }

    public function testHandleCommandUpdateNoLoc(): void
    {
        $data = [
            'id' => 111
        ];

        $command = ProcessAocCmd::create($data);

        $oc = m::mock(OperatingCentre::class)->makePartial();
        $application = m::mock(ApplicationEntity::class)->makePartial();

        $aoc = new ApplicationOperatingCentre($application, $oc);
        $aoc->setAction('U');
        $aoc->setIsInterim(true);
        $aoc->setNoOfVehiclesRequired(10);

        $aocs = [
            $aoc
        ];

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application->setLicence($licence);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('save')
            ->with($aoc)
            ->shouldReceive('findCorrespondingLoc')
            ->once()
            ->with($aoc, $licence)
            ->andReturn(null);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '0 licence operating centre(s) created',
                '0 licence operating centre(s) updated',
                '0 licence operating centre(s) removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertFalse($aoc->getIsInterim());
    }

    public function testHandleCommandDelete(): void
    {
        $data = [
            'id' => 111
        ];

        $command = ProcessAocCmd::create($data);

        $oc = m::mock(OperatingCentre::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();

        $aoc = new ApplicationOperatingCentre($application, $oc);
        $aoc->setAction('D');
        $aoc->setIsInterim(true);
        $aoc->setNoOfVehiclesRequired(10);

        $aocs = [
            $aoc
        ];

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        $loc = new LicenceOperatingCentre($licence, $oc);

        $application->setLicence($licence);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('save')
            ->with($aoc)
            ->shouldReceive('findCorrespondingLoc')
            ->once()
            ->with($aoc, $licence)
            ->andReturn($loc);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('delete')
            ->once()
            ->with($loc);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteConditionUndertakings::class,
            ['operatingCentre' => $oc, 'licence' => $licence],
            new Result()
        );
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteApplicationLinks::class,
            ['operatingCentre' => $oc],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '0 licence operating centre(s) created',
                '0 licence operating centre(s) updated',
                '1 licence operating centre(s) removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertFalse($aoc->getIsInterim());
    }

    public function testHandleCommandDeleteNoLoc(): void
    {
        $data = [
            'id' => 111
        ];

        $command = ProcessAocCmd::create($data);

        $oc = m::mock(OperatingCentre::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();

        $aoc = new ApplicationOperatingCentre($application, $oc);
        $aoc->setAction('D');
        $aoc->setIsInterim(true);
        $aoc->setNoOfVehiclesRequired(10);

        $aocs = [
            $aoc
        ];

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        $application->setLicence($licence);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('save')
            ->with($aoc)
            ->shouldReceive('findCorrespondingLoc')
            ->once()
            ->with($aoc, $licence)
            ->andReturnNull();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '0 licence operating centre(s) created',
                '0 licence operating centre(s) updated',
                '0 licence operating centre(s) removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertFalse($aoc->getIsInterim());
    }
}
