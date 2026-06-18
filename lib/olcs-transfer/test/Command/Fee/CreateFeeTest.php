<?php

namespace Dvsa\OlcsTest\Transfer\Command\Fee;

use Dvsa\Olcs\Transfer\Command\Fee\CreateFee;

/**
 * Create Fee test
 */
class CreateFeeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'foo' => 'bar',
            'application' => 111,
            'licence' => 222,
            'task' => 333,
            'amount' => '5.50',
            'invoicedDate' => '2015-01-01',
            'feeType' => 444,
            'description' => 'Some fee',
            'busReg' => 555,
            'irfoGvPermit' => 1,
            'irfoPsvAuth' => 2,
            'quantity' => 3,
            'irhpApplication' => 27,
            'irhpPermitApplication' => 440,
        ];

        $command = CreateFee::create($data);

        $this->assertEquals(111, $command->getApplication());
        $this->assertEquals(222, $command->getLicence());
        $this->assertEquals(333, $command->getTask());
        $this->assertEquals('5.50', $command->getAmount());
        $this->assertEquals('2015-01-01', $command->getInvoicedDate());
        $this->assertEquals(444, $command->getFeeType());
        $this->assertEquals('Some fee', $command->getDescription());
        $this->assertEquals('lfs_ot', $command->getFeeStatus());
        $this->assertEquals(555, $command->getBusReg());
        $this->assertEquals(1, $command->getIrfoGvPermit());
        $this->assertEquals(2, $command->getIrfoPsvAuth());
        $this->assertEquals(3, $command->getQuantity());
        $this->assertEquals(27, $command->getIrhpApplication());
        $this->assertEquals(440, $command->getIrhpPermitApplication());

        $this->assertEquals(
            [
                'application' => 111,
                'licence' => 222,
                'task' => 333,
                'amount' => '5.50',
                'invoicedDate' => '2015-01-01',
                'feeType' => 444,
                'description' => 'Some fee',
                'feeStatus' => 'lfs_ot',
                'busReg' => 555,
                'irfoGvPermit' => 1,
                'irfoPsvAuth' => 2,
                'quantity' => 3,
                'irhpApplication' => 27,
                'irhpPermitApplication' => 440,
            ],
            $command->getArrayCopy()
        );

        $command->exchangeArray(['feeStatus' => 'SomeOtherStatus']);

        $this->assertEquals('SomeOtherStatus', $command->getFeeStatus());
    }
}
