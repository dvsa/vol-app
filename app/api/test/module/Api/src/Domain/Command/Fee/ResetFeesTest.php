<?php

declare(strict_types=1);

/**
 * Reset Fees Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\ResetFees;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Reset Fees Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ResetFeesTest extends MockeryTestCase
{
    public function testStructure(): void
    {
        $fee1 = m::mock(Fee::class);
        $fee2 = m::mock(Fee::class);

        $data = [
            'fees' => [$fee1, $fee2],
        ];

        $command = ResetFees::create($data);

        $this->assertEquals([$fee1, $fee2], $command->getFees());
    }
}
