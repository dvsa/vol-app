<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking\Create as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Create as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);
        $this->mockRepo('Cases', \Dvsa\Olcs\Api\Domain\Repository\Cases::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'TYPE',
            'ATTACHED_TO',
            'ADDED_VIA',
            'cav_case',
            'cav_lic',
            'cav_app',
            'cu_cat_other'
        ];

        $this->references = [
            Cases::class => [
                24 => m::mock(Cases::class)
            ],
            Licence::class => [
                124 => m::mock(Licence::class)
            ],
            Application::class => [
                224 => m::mock(Application::class)
            ],
            OperatingCentre::class => [
                34 => m::mock(OperatingCentre::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandNoOperatingCentre(): void
    {
        $command = Command::create(
            ['attachedTo' => 'cat_oc']
        );

        $this->expectException(ValidationException::class);
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandNoCaseApplicationOrLicence(): void
    {
        $command = Command::create(
            []
        );

        $this->expectException(ValidationException::class);
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandCase(): void
    {
        $params = [
            'type' => 'TYPE',
            'fulfilled' => 'Y',
            'attachedTo' => 'ATTACHED_TO',
            'notes' => 'NOTES',
            'addedVia' => 'ADDED_VIA',
            'case' => 24,
            'conditionCategory' => 'cu_cat_other',
        ];

        $command = Command::create($params);

        $application = m::mock(Application::class)->makePartial();
        $licence = m::mock(Licence::class)->makePartial();
        $case = m::mock(Cases::class)->makePartial();
        $case->setLicence($licence);
        $case->setApplication($application);

        $this->repoMap['Cases']->shouldReceive('fetchById')->with(24)->once()->andReturn($case);
        $this->repoMap['ConditionUndertaking']->shouldReceive('save')->once()->andReturnUsing(
            function (ConditionUndertaking $cu) use ($params, $case) {
                $this->assertSame($this->refData[$params['type']], $cu->getConditionType());
                $this->assertSame($this->refData[$params['conditionCategory']], $cu->getConditionCategory());
                $this->assertSame($params['fulfilled'], $cu->getIsFulfilled());
                $this->assertSame('N', $cu->getIsDraft());
                $this->assertSame(null, $cu->getOperatingCentre());
                $this->assertSame($this->refData['cav_case'], $cu->getAddedVia());
                $this->assertSame($case, $cu->getCase());
                $this->assertSame(null, $cu->getAction());
                $this->assertSame($case->getLicence(), $cu->getLicence());
                $this->assertSame($case->getApplication(), $cu->getApplication());
                $cu->setId(76);
            }
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['conditionUndertaking' => 76], $response->getIds());
        $this->assertSame(['ConditionUndertaking created'], $response->getMessages());
    }

    public function testHandleCommandLicence(): void
    {
        $params = [
            'type' => 'TYPE',
            'fulfilled' => 'N',
            'attachedTo' => 'ATTACHED_TO',
            'notes' => 'NOTES',
            'addedVia' => 'ADDED_VIA',
            'licence' => 124,
            'operatingCentre' => 34,
            'conditionCategory' => 'cu_cat_other',
        ];

        $command = Command::create($params);

        $this->repoMap['ConditionUndertaking']->shouldReceive('save')->once()->andReturnUsing(
            function (ConditionUndertaking $cu) use ($params) {
                $this->assertSame($this->refData[$params['type']], $cu->getConditionType());
                $this->assertSame($this->refData[$params['conditionCategory']], $cu->getConditionCategory());
                $this->assertSame($params['fulfilled'], $cu->getIsFulfilled());
                $this->assertSame('N', $cu->getIsDraft());
                $this->assertSame($this->references[OperatingCentre::class][34], $cu->getOperatingCentre());
                $this->assertSame($this->refData['cav_lic'], $cu->getAddedVia());
                $this->assertSame($this->references[Licence::class][124], $cu->getLicence());
                $this->assertSame(null, $cu->getAction());
                $cu->setId(76);
            }
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['conditionUndertaking' => 76], $response->getIds());
        $this->assertSame(['ConditionUndertaking created'], $response->getMessages());
    }

    public function testHandleCommandApplication(): void
    {
        $params = [
            'type' => 'TYPE',
            'fulfilled' => 'N',
            'attachedTo' => 'ATTACHED_TO',
            'notes' => 'NOTES',
            'addedVia' => 'ADDED_VIA',
            'application' => 224,
            'operatingCentre' => 34,
            'conditionCategory' => 'cu_cat_other',
        ];

        $command = Command::create($params);

        $this->repoMap['ConditionUndertaking']->shouldReceive('save')->once()->andReturnUsing(
            function (ConditionUndertaking $cu) use ($params) {
                $this->assertSame($this->refData[$params['type']], $cu->getConditionType());
                $this->assertSame($this->refData[$params['conditionCategory']], $cu->getConditionCategory());
                $this->assertSame($params['fulfilled'], $cu->getIsFulfilled());
                $this->assertSame('Y', $cu->getIsDraft());
                $this->assertSame($this->references[OperatingCentre::class][34], $cu->getOperatingCentre());
                $this->assertSame($this->refData['cav_app'], $cu->getAddedVia());
                $this->assertSame(null, $cu->getLicence());
                $this->assertSame($this->references[Application::class][224], $cu->getApplication());
                $this->assertSame('A', $cu->getAction());
                $cu->setId(76);
            }
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['conditionUndertaking' => 76], $response->getIds());
        $this->assertSame(['ConditionUndertaking created'], $response->getMessages());
    }
}
