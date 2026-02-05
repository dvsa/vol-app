<?php

declare(strict_types=1);

/**
 * Process Duplicate Vehicle Warnings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\ProcessDuplicateVehicleWarning;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle\ProcessDuplicateVehicleWarnings;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Process Duplicate Vehicle Warnings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ProcessDuplicateVehicleWarningsTest extends AbstractCommandHandlerTestCase
{
    protected $dto;

    public function setUp(): void
    {
        $this->sut = new ProcessDuplicateVehicleWarnings();
        $this->dto = \Dvsa\Olcs\Api\Domain\Command\Vehicle\ProcessDuplicateVehicleWarnings::create([]);

        $this->mockRepo('LicenceVehicle', Repository\LicenceVehicle::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommandEmpty(): void
    {
        $results = null;

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchQueuedForWarning')
            ->andReturn($results);

        $result = $this->sut->handleCommand($this->dto);

        $expected = [
            'id' => [],
            'messages' => [
                'Nothing to process'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandNoDuplicates(): void
    {
        /** @var Licence $licence1 */
        $licence1 = m::mock(Licence::class)->makePartial();

        /** @var Vehicle $vehicle1 */
        $vehicle1 = m::mock(Vehicle::class)->makePartial();
        $vehicle1->setVrm('AB123');

        /** @var LicenceVehicle $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle1->setVehicle($vehicle1);
        $licenceVehicle1->setLicence($licence1);
        $licenceVehicle1->shouldReceive('removeDuplicateMark')->once();

        $results = [
            // No Duplicates
            $licenceVehicle1
        ];

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchQueuedForWarning')
            ->andReturn($results)
            ->shouldReceive('fetchDuplicates')
            ->with($licence1, 'AB123', false)
            ->andReturn(null)
            ->shouldReceive('save')
            ->once()
            ->with($licenceVehicle1);

        $result = $this->sut->handleCommand($this->dto);

        $expected = [
            'id' => [],
            'messages' => [
                '0 letter(s) sent',
                '1 record(s) no longer duplicates',
                '0 failed record(s)'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithDuplicates(): void
    {
        /** @var Licence $licence1 */
        $licence1 = m::mock(Licence::class)->makePartial();

        /** @var Vehicle $vehicle1 */
        $vehicle1 = m::mock(Vehicle::class)->makePartial();
        $vehicle1->setVrm('AB123');

        /** @var LicenceVehicle $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle1->setVehicle($vehicle1);
        $licenceVehicle1->setLicence($licence1);
        $licenceVehicle1->setId(111);

        $results = [
            // No Duplicates
            $licenceVehicle1
        ];

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchQueuedForWarning')
            ->andReturn($results)
            ->shouldReceive('fetchDuplicates')
            ->with($licence1, 'AB123', false)
            ->andReturn(['foo' => 'bar']);

        $result1 = new Result();
        $result1->addMessage('ProcessDuplicateVehicleWarning');
        $this->expectedSideEffect(ProcessDuplicateVehicleWarning::class, ['id' => 111], $result1);

        $result = $this->sut->handleCommand($this->dto);

        $expected = [
            'id' => [],
            'messages' => [
                'ProcessDuplicateVehicleWarning',
                '111 succeeded',
                '1 letter(s) sent',
                '0 record(s) no longer duplicates',
                '0 failed record(s)'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithFailure(): void
    {
        /** @var Licence $licence1 */
        $licence1 = m::mock(Licence::class)->makePartial();

        /** @var Vehicle $vehicle1 */
        $vehicle1 = m::mock(Vehicle::class)->makePartial();
        $vehicle1->setVrm('AB123');

        /** @var LicenceVehicle $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle1->setVehicle($vehicle1);
        $licenceVehicle1->setLicence($licence1);
        $licenceVehicle1->setId(111);

        $results = [
            // No Duplicates
            $licenceVehicle1
        ];

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchQueuedForWarning')
            ->andReturn($results)
            ->shouldReceive('fetchDuplicates')
            ->with($licence1, 'AB123', false)
            ->andReturn(['foo' => 'bar']);

        $exception = new \Exception('foo');
        $this->expectedSideEffectThrowsException(ProcessDuplicateVehicleWarning::class, ['id' => 111], $exception);

        $result = $this->sut->handleCommand($this->dto);

        $expected = [
            'id' => [],
            'messages' => [
                '111 failed: foo',
                '0 letter(s) sent',
                '0 record(s) no longer duplicates',
                '1 failed record(s)'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
