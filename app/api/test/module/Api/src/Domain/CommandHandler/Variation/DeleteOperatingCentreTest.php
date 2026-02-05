<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\DeleteOperatingCentre;
use Dvsa\Olcs\Transfer\Command\Variation\DeleteOperatingCentre as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Licence\DeleteOperatingCentres as LicenceDeleteOperatingCentres;
use Dvsa\Olcs\Api\Domain\Command\Application\HandleOcVariationFees as HandleOcVariationFeesCmd;

/**
 * Delete Operating Centres Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeleteOperatingCentreTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteOperatingCentre();
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('ApplicationOperatingCentre', Repository\ApplicationOperatingCentre::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [
            ApplicationOperatingCentre::class => [
                22 => m::mock(ApplicationOperatingCentre::class)
            ],
            LicenceOperatingCentre::class => [
                22 => m::mock(LicenceOperatingCentre::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandAppInvalid(): void
    {
        $data = [
            'id' => 'A22',
            'application' => 111
        ];
        $command = Cmd::create($data);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($application);

        $this->references[ApplicationOperatingCentre::class][22]->setAction('D');

        $this->expectException(BadRequestException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandLicInvalid(): void
    {
        $data = [
            'id' => 'L22',
            'application' => 111
        ];
        $command = Cmd::create($data);

        $aoc = m::mock();

        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('getOperatingCentres->matching')
            ->andReturn($aocs);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($application);

        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();

        $this->references[LicenceOperatingCentre::class][22]->setOperatingCentre($oc);

        $this->expectException(BadRequestException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandAppValid(): void
    {
        $applicationId = 111;
        $data = [
            'id' => 'A22',
            'application' => $applicationId
        ];
        $command = Cmd::create($data);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId($applicationId);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with($applicationId)
            ->andReturn($application);

        $this->references[ApplicationOperatingCentre::class][22]->setAction('A');

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('delete')
            ->once()
            ->with($this->references[ApplicationOperatingCentre::class][22]);

        $data = [
            'id' => $applicationId,
            'section' => 'operatingCentres'
        ];
        $result = new Result();
        $result->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result);

        $this->expectedSideEffect(HandleOcVariationFeesCmd::class, ['id' => $applicationId], new Result());

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Removed application operating centre delta record',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandAppCannotDelete(): void
    {
        $data = [
            'id' => 'A22',
            'application' => 111
        ];
        $command = Cmd::create($data);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(111);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($application);

        $this->references[ApplicationOperatingCentre::class][22]->setAction('A');
        $this->references[ApplicationOperatingCentre::class][22]->shouldReceive('checkCanDelete')->with()->once()
            ->andReturn(['ERROR' => 'Foo']);

        $this->expectException(BadRequestException::class);
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandLicValid(): void
    {
        $applicationId = 111;
        $data = [
            'id' => 'L22',
            'application' => 111
        ];
        $command = Cmd::create($data);

        $aocs = new ArrayCollection();

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId($applicationId);
        $application->shouldReceive('getOperatingCentres->matching')
            ->andReturn($aocs);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with($applicationId)
            ->andReturn($application);

        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();

        $this->references[LicenceOperatingCentre::class][22]->setOperatingCentre($oc);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('save')
            ->once()
            ->with(m::type(ApplicationOperatingCentre::class));

        $data = [
            'id' => $applicationId,
            'section' => 'operatingCentres'
        ];
        $result = new Result();
        $result->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result);

        $this->expectedSideEffect(HandleOcVariationFeesCmd::class, ['id' => $applicationId], new Result());

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Created application operating centre delta record',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandCannotDelete(): void
    {
        $data = [
            'id' => 'L22',
            'application' => 111
        ];
        $command = Cmd::create($data);

        $aocs = new ArrayCollection();

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(111);
        $application->shouldReceive('getOperatingCentres->matching')
            ->andReturn($aocs);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($application);

        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();

        $this->references[LicenceOperatingCentre::class][22]->setOperatingCentre($oc);
        $this->references[LicenceOperatingCentre::class][22]->shouldReceive('checkCanDelete')->with()->once()
            ->andReturn(['ERROR' => 'Foo']);

        $this->expectException(BadRequestException::class);

        $this->sut->handleCommand($command);
    }
}
