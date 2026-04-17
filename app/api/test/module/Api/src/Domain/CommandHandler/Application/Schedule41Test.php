<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Command\Application\Schedule41 as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre as LicenceOperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre as ApplicationOperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Schedule41;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\CreateS4 as CreateC4Cmd;
use Dvsa\Olcs\Api\Domain\Command\LicenceOperatingCentre\AssociateS4 as AssociateS4Cmd;
use Dvsa\Olcs\Api\Domain\Command\ApplicationOperatingCentre\CreateApplicationOperatingCentre as CreateAocCmd;
use Dvsa\Olcs\Api\Domain\Command\Cases\ConditionUndertaking\CreateConditionUndertaking as CreateCuCmd;

/**
 * Schedule41 Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Schedule41Test extends AbstractCommandHandlerTestCase
{
    protected $application;
    protected $licence;
    protected $loc;

    public function setUp(): void
    {
        $this->sut = new Schedule41();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->application = m::mock(ApplicationEntity::class)->makePartial();
        $this->licence = m::mock(LicenceEntity::class)->makePartial();
        $this->loc = m::mock(LicenceOperatingCentreEntity::class)->makePartial();
        $this->references = [
            ApplicationEntity::class => [
                1 => $this->application
            ],
            LicenceEntity::class => [
                2 => $this->licence
            ],
            LicenceOperatingCentreEntity::class => [
                3 => $this->loc
            ]
        ];
        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $appId = 1;
        $data = [
            'id' => $appId,
            'licence' => 2,
            'operatingCentres' => [3],
            'surrenderLicence' => 'Y',
        ];
        $command = Cmd::create($data);

        $newS4Id = 3;
        $result = new Result();
        $result->addMessage('S4 created');
        $result->addId('s4', $newS4Id);

        $createS4Data = [
            'application' => $this->application,
            'licence' => $this->licence,
            'surrenderLicence' => 'Y',
            'receivedDate' => new DateTime('now')
        ];
        $this->expectedSideEffect(CreateC4Cmd::class, $createS4Data, $result);

        $result1 = new Result();
        $result1->addMessage('S4 associated');

        $associateS4Data = [
            's4' => $newS4Id,
            'licenceOperatingCentres' => [3]
        ];
        $this->expectedSideEffect(AssociateS4Cmd::class, $associateS4Data, $result1);

        $result2 = new Result();
        $result2->addMessage('Loc created');

        $conditionUndertakings = new ArrayCollection();

        $cu1 = m::mock()
            ->shouldReceive('getLicence')
            ->andReturnNull()
            ->once()
            ->getMock();
        $conditionUndertakings->add($cu1);

        $cuTypeId = 5;
        $cuNotes = 'CUNOTES';
        $cu2 = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn('LIC')
            ->once()
            ->shouldReceive('getConditionType')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn($cuTypeId)
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('getNotes')
            ->andReturn($cuNotes)
            ->once()
            ->getMock();
        $conditionUndertakings->add($cu2);

        $locId = 9;
        $operatingCentre = m::mock()
            ->shouldReceive('getConditionUndertakings')
            ->andReturn($conditionUndertakings)
            ->once()
            ->shouldReceive('getId')
            ->andReturn($locId)
            ->once()
            ->getMock();

        $this->loc
            ->shouldReceive('getOperatingCentre')
            ->andReturn($operatingCentre)
            ->times(3)
            ->getMock();

        $this->loc->shouldReceive('getNoOfVehiclesRequired')->andReturn(1)->once()->getMock();
        $this->loc->shouldReceive('getNoOfTrailersRequired')->andReturn(2)->once()->getMock();
        $createAocData = [
            'application' => $this->application,
            's4' => $newS4Id,
            'operatingCentre' => $operatingCentre,
            'action' => 'A',
            'adPlaced' => ApplicationOperatingCentreEntity::AD_POST,
            'noOfVehiclesRequired' => 1,
            'noOfTrailersRequired' => 2,
        ];
        $this->expectedSideEffect(CreateAocCmd::class, $createAocData, $result2);

        $this->application
            ->shouldReceive('getId')
            ->andReturn($appId)
            ->once()
            ->getMock();

        $createCuData = [
            'licence' => null,
            'application' => $appId,
            'operatingCentre' => $locId,
            'conditionType' => $cuTypeId,
            'addedVia' => ConditionUndertakingEntity::ADDED_VIA_APPLICATION,
            'action' => 'A',
            'attachedTo' => ConditionUndertakingEntity::ATTACHED_TO_OPERATING_CENTRE,
            'isDraft' => 'Y',
            'isFulfilled' => 'N',
            's4' => $newS4Id,
            'notes' => $cuNotes
        ];

        $result3 = new Result();
        $result3->addMessage('Condition Undertaking created');

        $this->expectedSideEffect(CreateCuCmd::class, $createCuData, $result3);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                's4' => 3
            ],
            'messages' => [
                'S4 created',
                'S4 associated',
                'Loc created',
                'Condition Undertaking created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
