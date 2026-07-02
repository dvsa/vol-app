<?php

namespace Dvsa\OlcsTest\Transfer\Command\Transaction;

use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction;

/**
 * Complete Transaction test
 */
class CompleteTransactionTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'reference' => 'OLCS-1234-ABCD',
            'paymentMethod' => 'fpm_card_online',
            'cpmsData' => ['foo' => 'bar'],
            'submitApplicationId' => 69,
        ];

        $command = CompleteTransaction::create($data);

        $this->assertEquals('OLCS-1234-ABCD', $command->getReference());
        $this->assertEquals('fpm_card_online', $command->getPaymentMethod());
        $this->assertEquals(['foo' => 'bar'], $command->getCpmsData());
        $this->assertEquals(69, $command->getSubmitApplicationId());
    }
}
