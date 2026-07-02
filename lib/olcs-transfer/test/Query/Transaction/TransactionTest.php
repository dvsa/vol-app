<?php

namespace Dvsa\OlcsTest\Transfer\Query\Transaction;

use Dvsa\Olcs\Transfer\Query\Transaction\Transaction;

/**
 * Transaction Test
 */
class TransactionTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = Transaction::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $query->getId());
    }
}
