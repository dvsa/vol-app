<?php

declare(strict_types=1);

/**
 * Create NonPi Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\NonPi;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\NonPi\Create as CreateCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\NonPi;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Cases\Hearing as NonPiEntity;
use Dvsa\Olcs\Transfer\Command\Cases\NonPi\Create as CreateCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create NonPi Test
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var CreateCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CreateCommandHandler();
        $this->mockRepo('NonPi', NonPi::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'non_pi_type_off_proc',
            'non_pio_nfa'
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
        $data = [
            "case" => "50",
            "nonPiType" => "non_pi_type_off_proc",
            "venue" => "2",
            "presidingStaffName" => "John Smith",
            "agreedByTcDate" => "2015-01-01 12:15",
            "hearingDate" => "2015-02-01 14:15",
            "venueOther" => "Some Other Venue",
            "witnessCount" => $inputWitnesses,
            "outcome" => "non_pio_nfa"
        ];

        $command = CreateCommand::create($data);

        /** @var $conv NonPiEntity */
        $pro = null;

        $this->repoMap['NonPi']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(NonPiEntity::class))
            ->andReturnUsing(
                function (NonPiEntity $entity) use (&$pro) {
                    $entity->setId(111);
                    $pro = $entity;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'non-pi' => 111,
            ],
            'messages' => [
                'Created'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());

        /** @var NonPiEntity $pro */
        $this->assertEquals(111, $pro->getId());
        $this->assertEquals(50, $pro->getCase()->getId());
        $this->assertEquals($outputWitnesses, $pro->getWitnessCount());
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
