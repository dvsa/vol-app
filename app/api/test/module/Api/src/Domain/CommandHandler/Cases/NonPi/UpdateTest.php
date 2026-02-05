<?php

declare(strict_types=1);

/**
 * Update NonPi Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\NonPi;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\NonPi\Update as UpdateCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\NonPi;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Cases\Hearing as NonPiEntity;
use Dvsa\Olcs\Transfer\Command\Cases\NonPi\Update as UpdateCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update NonPi Test
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var UpdateCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new UpdateCommandHandler();
        $this->mockRepo('NonPi', NonPi::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'non_pi_type_off_proc',
        ];

        $this->references = [
            Entity\Cases\Cases::class => [
                50 => m::mock(Entity\Cases\Cases::class)
            ],
            Entity\Venue::class => [
                2 => m::mock(Entity\Venue::class)
            ]
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpWitnessProvider')]
    public function testHandleCommand(mixed $inputWitnesses, mixed $outputWitnesses): void
    {
        $id = 150;
        $version = 2;

        $agreedByTcDate = "2015-02-01 14:15";
        $agreedByTcDateTime = new \DateTime($agreedByTcDate);
        $hearingDate = "2015-02-01 14:15";
        $hearingDateTime = new \DateTime($hearingDate);

        $data = [
            "id" => $id,
            "version" => $version,
            "case" => "50",
            "nonPiType" => "non_pi_type_off_proc",
            "venue" => "2",
            "presidingStaffName" => "Ed",
            "agreedByTcDate" => $agreedByTcDate,
            "hearingDate" => $hearingDate,
            "witnessCount" => $inputWitnesses,
            "outcome" => "non_pio_nfa"
        ];

        $command = UpdateCommand::create($data);

        $this->repoMap['NonPi']
            ->shouldReceive('fetchById')
            ->with($id, \Doctrine\Orm\Query::HYDRATE_OBJECT, $version)
            ->andReturn(
                m::mock(NonPiEntity::class)
                    ->shouldReceive('setHearingType')
                    ->andReturn(
                        m::mock(Entity\System\RefData::class)
                            ->shouldReceive('getId')
                            ->andReturn('non_pi_type_off_proc')
                            ->getMock()
                    )
                    ->shouldReceive('getId')
                    ->andReturn($id)
                    ->shouldReceive('setCase')
                    ->with(m::type(Entity\Cases\Cases::class))
                    ->shouldReceive('setVenue')
                    ->with(m::type(Entity\Venue::class))
                    ->shouldReceive('setVenueOther')
                    ->with(null)
                    ->shouldReceive('setPresidingStaffName')
                    ->with('Ed')
                    ->shouldReceive('processDate')
                    ->with($agreedByTcDate)
                    ->once()
                    ->andReturn($agreedByTcDateTime)
                    ->shouldReceive('setAgreedByTcDate')
                    ->with($agreedByTcDateTime)
                    ->shouldReceive('processDate')
                    ->with($hearingDate, \DateTime::ISO8601, false)
                    ->once()
                    ->andReturn($hearingDateTime)
                    ->shouldReceive('setHearingDate')
                    ->with($hearingDateTime)
                    ->shouldReceive('setWitnessCount')
                    ->with($outputWitnesses)
                    ->shouldReceive('setOutcome')
                    ->andReturn(
                        m::mock(Entity\System\RefData::class)
                            ->shouldReceive('getId')
                            ->andReturn('non_pio_nfa')
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('save')
            ->once();

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'non-pi' => $id,
            ],
            'messages' => [
                'Updated'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());
    }

    /**
     * expected witness input and output values
     */
    public static function dpWitnessProvider(): array
    {
        return [
            [4, 4],
            [1, 1],
            [0, 0],
            [null, 0]
        ];
    }
}
