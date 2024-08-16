<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateVehicleDeclaration as CommandHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleDeclaration as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;

/**
 * UpdateVehicleDeclarationTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateVehicleDeclarationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [
            'ltyp_r',
        ];

        parent::initReferences();
    }

    protected function setupApplication()
    {
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );

        $application = new \Dvsa\Olcs\Api\Entity\Application\Application(
            $licence,
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            1
        );

        $application->setId(423);
        $application->setLicenceType($this->refData['ltyp_r']);

        return $application;
    }

    public function testHandleCommand()
    {
        $command = Cmd::create(
            [
                'id' => 423,
                'version' => 32,
                'psvNoSmallVhlConfirmation' => '1',
                'psvOperateSmallVhl' => '2',
                'psvSmallVhlConfirmation' => '3',
                'psvSmallVhlNotes' => '4',
                'psvMediumVhlConfirmation' => '5',
                'psvMediumVhlNotes' => '6',
                'psvLimousines' => '7',
                'psvNoLimousineConfirmation' => '8',
                'psvOnlyLimousinesConfirmation' => '9',
            ]
        );

        $application = $this->setupApplication();
        $this->repoMap['Application']->shouldReceive('fetchUsingId')->once()->with($command, Query::HYDRATE_OBJECT, 32)
            ->andReturn($application)
            ->shouldReceive('save')->with($application)->once();

        $this->expectedSideEffect(
            UpdateApplicationCompletionCmd::class,
            ['id' => 423, 'section' => 'vehiclesDeclarations'],
            new Result()
        );

        $this->sut->handleCommand($command);

        $this->assertSame('1', $application->getPsvNoSmallVhlConfirmation());
        $this->assertSame('2', $application->getPsvOperateSmallVhl());
        $this->assertSame('3', $application->getPsvSmallVhlConfirmation());
        $this->assertSame('4', $application->getPsvSmallVhlNotes());
        $this->assertSame('5', $application->getPsvMediumVhlConfirmation());
        $this->assertSame('6', $application->getPsvMediumVhlNotes());
        $this->assertSame('7', $application->getPsvLimousines());
        $this->assertSame('8', $application->getPsvNoLimousineConfirmation());
        $this->assertSame('9', $application->getPsvOnlyLimousinesConfirmation());
    }
}
