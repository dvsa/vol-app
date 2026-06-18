<?php

namespace Dvsa\OlcsTest\Transfer\Command\Transaction;

use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;

/**
 * Pay Outstanding Fees test
 */
class PayOutstandingFeesTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'feeIds' => [1, 2],
            'organisationId' => 69,
            'applicationId' => 99,
            'irhpApplication' => 8,
            'cpmsRedirectUrl' => 'http://olcs-selfserve/foo',
            'paymentMethod' => 'fpm_card_online',
            'received' => '1234.56',
            'receiptDate' => '2015-06-18',
            'payer' => 'Dan',
            'slipNo' => '1234',
            'chequeNo' => '2345',
            'chequeDate' => '2015-06-17',
            'poNo' => '3456',
            'customerReference' => 'foo',
            'customerName' => 'bar',
            'address' => 'cake',
            'shouldResolveOnly' => true,
        ];

        $command = PayOutstandingFees::create($data);

        $this->assertEquals([1, 2], $command->getFeeIds());
        $this->assertEquals(69, $command->getOrganisationId());
        $this->assertEquals(99, $command->getApplicationId());
        $this->assertEquals(8, $command->getIrhpApplication());
        $this->assertEquals('http://olcs-selfserve/foo', $command->getCpmsRedirectUrl());
        $this->assertEquals('fpm_card_online', $command->getPaymentMethod());
        $this->assertEquals('1234.56', $command->getReceived());
        $this->assertEquals('2015-06-18', $command->getReceiptDate());
        $this->assertEquals('Dan', $command->getPayer());
        $this->assertEquals('1234', $command->getSlipNo());
        $this->assertEquals('2345', $command->getChequeNo());
        $this->assertEquals('2015-06-17', $command->getChequeDate());
        $this->assertEquals('3456', $command->getPoNo());
        $this->assertEquals('foo', $command->getCustomerReference());
        $this->assertEquals('bar', $command->getCustomerName());
        $this->assertEquals('cake', $command->getAddress());
        $this->assertTrue($command->getShouldResovleOnly());
    }
}
