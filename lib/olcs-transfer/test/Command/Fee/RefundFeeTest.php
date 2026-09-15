<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Fee;

use Dvsa\Olcs\Transfer\Command\Fee\RefundFee;

/**
 * Refund fee test
 */
final class RefundFeeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 1,
            'customerReference' => 'foo',
            'customerName' => 'bar',
            'address' => 'cake'
        ];

        $command = RefundFee::create($data);

        $this->assertEquals(1, $command->getId());
        $this->assertEquals('foo', $command->getCustomerReference());
        $this->assertEquals('bar', $command->getCustomerName());
        $this->assertEquals('cake', $command->getAddress());
    }
}
