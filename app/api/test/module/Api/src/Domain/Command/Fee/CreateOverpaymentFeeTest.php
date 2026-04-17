<?php

declare(strict_types=1);

/**
 * Create Overpayment Fee Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\CreateOverpaymentFee;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Create Overpayment Fee Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CreateOverpaymentFeeTest extends MockeryTestCase
{
    public function testStructure(): void
    {
        $fee1 = m::mock(Fee::class);
        $fee2 = m::mock(Fee::class);

        $data = [
            'receivedAmount' => '9.99',
            'fees' => [$fee1, $fee2],
        ];

        $command = CreateOverpaymentFee::create($data);

        $this->assertEquals('9.99', $command->getReceivedAmount());
        $this->assertEquals([$fee1, $fee2], $command->getFees());
    }
}
