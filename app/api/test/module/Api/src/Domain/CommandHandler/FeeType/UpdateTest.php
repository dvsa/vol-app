<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\FeeType;

use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\FeeType\Update as UpdateHandler;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\FeeType\Update as UpdateCmd;

/**
 * Update FeeType Command Handler test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateHandler();
        $this->mockRepo('FeeType', FeeTypeRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $cmdData = [
            'id' => 12111,
            'fixedValue' => 0,
            'annualValue' => 10,
            'fiveYearValue' => 0,
            'effectiveFrom' => '2050-12-26T00:00:00+0000'
        ];

        $command = UpdateCmd::create($cmdData);

        $existingFeeType = m::mock(FeeType::class);
        $newFeeType = m::mock(FeeType::class);
        $this->repoMap['FeeType']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($existingFeeType);

        $existingFeeType->shouldReceive('updateNewFeeType')
            ->with(
                $command->getEffectiveFrom(),
                $command->getFixedValue(),
                $command->getAnnualValue(),
                $command->getFiveYearValue(),
                $existingFeeType
            )
            ->andReturn($newFeeType);

        $this->repoMap['FeeType']
            ->shouldReceive('save')
            ->once()
            ->with($newFeeType);

        $newFeeType->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(121212);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'FeeType' => 121212
            ],
            'messages' => ['Fee Type updated'],
        ];

        self::assertEquals($expected, $result->toArray());
    }
}
