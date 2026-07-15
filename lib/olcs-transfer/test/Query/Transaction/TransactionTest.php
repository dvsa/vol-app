<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Transaction;

use Dvsa\Olcs\Transfer\Query\Transaction\Transaction;

/**
 * Transaction Test
 */
final class TransactionTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = Transaction::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $query->getId());
    }
}
