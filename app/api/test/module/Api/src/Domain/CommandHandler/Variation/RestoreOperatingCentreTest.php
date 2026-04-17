<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\RestoreOperatingCentre;
use Dvsa\Olcs\Transfer\Command\Variation\RestoreOperatingCentre as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Application\HandleOcVariationFees as HandleOcVariationFeesCmd;

/**
 * Delete Operating Centres Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RestoreOperatingCentreTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RestoreOperatingCentre();
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('ApplicationOperatingCentre', Repository\ApplicationOperatingCentre::class);
        $this->mockRepo('LicenceOperatingCentre', Repository\LicenceOperatingCentre::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommandAppValid(): void
    {
        $applicationId = 111;
        $data = [
            'id' => 'A11',
            'application' => $applicationId
        ];
        $command = Cmd::create($data);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('D');

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('fetchById')
            ->with(11)
            ->andReturn($aoc)
            ->shouldReceive('delete')
            ->once()
            ->with($aoc);

        $data = [
            'id' => $applicationId,
            'section' => 'operatingCentres',
        ];
        $result = new Result();
        $result->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result);

        $this->expectedSideEffect(HandleOcVariationFeesCmd::class, ['id' => $applicationId], new Result());

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Delta record removed',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandAppInvalid(): void
    {
        $data = [
            'id' => 'A11'
        ];
        $command = Cmd::create($data);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('A');

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('fetchById')
            ->with(11)
            ->andReturn($aoc);

        $this->expectException(ForbiddenException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandLicInvalid(): void
    {
        $data = [
            'id' => 'L11',
            'application' => 111,
        ];
        $command = Cmd::create($data);

        $deltas = new ArrayCollection();

        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('getDeltaAocByOc')
            ->with($oc)
            ->andReturn($deltas);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($application);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc->setOperatingCentre($oc);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('fetchById')
            ->with(11)
            ->andReturn($loc);

        $this->expectException(ForbiddenException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandLicValid(): void
    {
        $applicationId = 111;
        $data = [
            'id' => 'L11',
            'application' => $applicationId,
        ];
        $command = Cmd::create($data);

        $aoc = m::mock();

        $deltas = new ArrayCollection();
        $deltas->add($aoc);

        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId($applicationId);
        $application->shouldReceive('getDeltaAocByOc')
            ->with($oc)
            ->andReturn($deltas);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with($applicationId)
            ->andReturn($application);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc->setOperatingCentre($oc);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('fetchById')
            ->with(11)
            ->andReturn($loc);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('delete')
            ->once()
            ->with($aoc);

        $data = [
            'id' => $applicationId,
            'section' => 'operatingCentres',
        ];
        $result = new Result();
        $result->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result);

        $this->expectedSideEffect(HandleOcVariationFeesCmd::class, ['id' => $applicationId], new Result());

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 Delta record(s) removed',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
