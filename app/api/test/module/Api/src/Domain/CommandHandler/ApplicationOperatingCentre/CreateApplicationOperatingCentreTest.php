<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationOperatingCentre;

use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\ApplicationOperatingCentre\CreateApplicationOperatingCentre as CreateAocCmd;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre as ApplicationOperatingCentreRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\Application\S4 as S4Entity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre as ApplicationOperatingCentreEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationOperatingCentre\CreateApplicationOperatingCentre;

/**
 * Create Application Operating Centre Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateApplicationOperatingCentreTest extends AbstractCommandHandlerTestCase
{
    protected $s4;

    public function setUp(): void
    {
        $this->sut = new CreateApplicationOperatingCentre();
        $this->mockRepo('ApplicationOperatingCentre', ApplicationOperatingCentreRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->s4 = m::mock(S4Entity::class)->makePartial();
        $this->references = [
            S4Entity::class => [
                1 => $this->s4
            ],
        ];
        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $mockApplication = m::mock(ApplicationEntity::class);
        $mockOperatingCentre = m::mock(OperatingCentreEntity::class);
        $data = [
            'application' => $mockApplication,
            'operatingCentre' => $mockOperatingCentre,
            'noOfVehiclesRequired' => 1,
            'noOfTrailersRequired' => 2,
            's4' => 1
        ];
        $command = CreateAocCmd::create($data);

        /** @var ApplicationOperatingCentreEntity $savedFee */
        $savedAoc = null;

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('save')
            ->once()
            ->with(m::type(ApplicationOperatingCentreEntity::class))
            ->andReturnUsing(
                function (ApplicationOperatingCentreEntity $aoc) use (&$savedAoc) {
                    $aoc->setId(111);
                    $savedAoc = $aoc;
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertSame($mockApplication, $savedAoc->getApplication());
        $this->assertSame($mockOperatingCentre, $savedAoc->getOperatingCentre());
        $this->assertEquals(ApplicationOperatingCentre::ACTION_ADD, $savedAoc->getAction());
        $this->assertEquals(ApplicationOperatingCentre::AD_POST, $savedAoc->getAdPlaced());
        $this->assertEquals(1, $savedAoc->getNoOfVehiclesRequired());
        $this->assertEquals(2, $savedAoc->getNoOfTrailersRequired());
        $this->assertSame($this->s4, $savedAoc->getS4());
        $this->assertEquals(111, $savedAoc->getId());

        $expected = [
            'id' => [],
            'messages' => ['Application operating centre saved.']
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
