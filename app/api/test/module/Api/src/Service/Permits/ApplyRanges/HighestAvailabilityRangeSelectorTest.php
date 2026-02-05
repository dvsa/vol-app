<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\HighestAvailabilityRangeSelector;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * HighestAvailabilityRangeSelectorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class HighestAvailabilityRangeSelectorTest extends MockeryTestCase
{
    private $highestAvailabilityRangeSelector;

    private $result;

    public function setUp(): void
    {
        $this->highestAvailabilityRangeSelector = new HighestAvailabilityRangeSelector();

        $this->result = new Result();
    }

    public function testGetRangeWhereSingleHighestAvailabilityRange(): void
    {
        $expectedRange = $this->createMockRange(4, 45);

        $ranges = [
            $this->createMockRange(2, 25),
            $expectedRange,
            $this->createMockRange(7, 35)
        ];

        $this->assertEquals(
            $expectedRange,
            $this->highestAvailabilityRangeSelector->getRange($this->result, $ranges)
        );

        $expectedMessages = [
            '    - selecting range with most permits remaining from ranges 2,4,7:',
            '    - Using range 4 with 45 permits remaining'
        ];

        $this->assertEquals(
            $expectedMessages,
            $this->result->getMessages()
        );
    }

    public function testGetRangeWhereMultipleHighestAvailabilityRanges(): void
    {
        $expectedRange = $this->createMockRange(5, 45);

        $ranges = [
            $this->createMockRange(30, 45),
            $this->createMockRange(15, 35),
            $this->createMockRange(40, 35),
            $this->createMockRange(10, 45),
            $expectedRange,
            $this->createMockRange(45, 10)
        ];

        $this->assertEquals(
            $expectedRange,
            $this->highestAvailabilityRangeSelector->getRange($this->result, $ranges)
        );

        $expectedMessages = [
            '    - selecting range with most permits remaining from ranges 30,15,40,10,5,45:',
            '    - multiple ranges 30,10,5 all have the most number of permits remaining: 45',
            '    - using range with lowest id instead - range id is 5'
        ];

        $this->assertEquals(
            $expectedMessages,
            $this->result->getMessages()
        );
    }

    private function createMockRange(mixed $id, mixed $permitsRemaining): array
    {
        $entity = m::mock(IrhpPermitRange::class);
        $entity->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($id);

        return [
            'entity' => $entity,
            'permitsRemaining' => $permitsRemaining
        ];
    }
}
