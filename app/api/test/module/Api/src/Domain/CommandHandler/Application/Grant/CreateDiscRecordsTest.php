<?php

declare(strict_types=1);

/**
 * Create Disc Records Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\Grant;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreateDiscRecords as CreateDiscRecordsCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsDiscs;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant\CreateDiscRecords;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvDiscs;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create Disc Records Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateDiscRecordsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateDiscRecords();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111,
            'currentTotAuth' => 5
        ];

        $command = CreateDiscRecordsCmd::create($data);

        $activeVehicles = new ArrayCollection();

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);

        $application->updateTotAuthHgvVehicles(10);

        $application->shouldReceive('isPsv')
            ->andReturn(true)
            ->shouldReceive('getActiveVehicles')
            ->andReturn($activeVehicles);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $result1 = new Result();
        $result1->addMessage('CreatePsvDiscs');
        $this->expectedSideEffect(CreatePsvDiscs::class, ['licence' => 222, 'amount' => 5, 'isCopy' => 'N'], $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CreatePsvDiscs'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithLicenceVehicles(): void
    {
        $data = [
            'id' => 111,
            'currentTotAuth' => 5
        ];

        $command = CreateDiscRecordsCmd::create($data);

        /** @var LicenceVehicle $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle1->setSpecifiedDate(new \DateTime('2015-01-01'));
        /** @var LicenceVehicle $licenceVehicle2 */
        $licenceVehicle2 = m::mock(LicenceVehicle::class)->makePartial();

        $activeVehicles = new ArrayCollection();
        $activeVehicles->add($licenceVehicle1);
        $activeVehicles->add($licenceVehicle2);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);

        $application->updateTotAuthHgvVehicles(10);

        $application->shouldReceive('isPsv')
            ->andReturn(true)
            ->shouldReceive('isGoods')
            ->andReturn(false)
            ->shouldReceive('getActiveVehicles')
            ->andReturn($activeVehicles);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addMessage('CreatePsvDiscs');
        $this->expectedSideEffect(CreatePsvDiscs::class, ['licence' => 222, 'amount' => 5, 'isCopy' => 'N'], $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CreatePsvDiscs'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('2015-01-01', $licenceVehicle1->getSpecifiedDate()->format('Y-m-d'));
        $this->assertEquals(date('Y-m-d'), $licenceVehicle2->getSpecifiedDate()->format('Y-m-d'));
        $this->assertEquals(null, $licenceVehicle1->getInterimApplication());
        $this->assertEquals(null, $licenceVehicle2->getInterimApplication());
    }

    public function testHandleCommandWithLicenceVehiclesGoods(): void
    {
        $data = [
            'id' => 111,
            'currentTotAuth' => 5
        ];

        $command = CreateDiscRecordsCmd::create($data);

        /** @var LicenceVehicle $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle1->setId(123);
        $licenceVehicle1->setSpecifiedDate(new \DateTime('2015-01-01'));
        /** @var LicenceVehicle $licenceVehicle2 */
        $licenceVehicle2 = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle2->setId(124);

        $activeVehicles = new ArrayCollection();
        $activeVehicles->add($licenceVehicle1);
        $activeVehicles->add($licenceVehicle2);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);

        $application->updateTotAuthHgvVehicles(10);

        $application->shouldReceive('isPsv')
            ->andReturn(false)
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->shouldReceive('getActiveVehicles')
            ->andReturn($activeVehicles);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addMessage('CreateGoodsDiscs');
        $this->expectedSideEffect(CreateGoodsDiscs::class, ['ids' => [123, 124], 'isCopy' => 'N'], $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CreateGoodsDiscs'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
