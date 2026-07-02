<?php

namespace Dvsa\OlcsTest\Transfer\Command\Transaction;

use Dvsa\Olcs\Transfer\Command\Transaction\ReverseTransaction;

/**
 * Reverse transaction test
 */
class ReverseTransactionTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 1,
            'reason' => 'foo',
            'customerReference' => 'bar',
            'customerName' => 'cake',
            'address' => 'baz',
        ];

        $command = ReverseTransaction::create($data);

        $this->assertEquals(1, $command->getId());
        $this->assertEquals('foo', $command->getReason());
        $this->assertEquals('bar', $command->getCustomerReference());
        $this->assertEquals('cake', $command->getCustomerName());
        $this->assertEquals('baz', $command->getAddress());
    }
}
