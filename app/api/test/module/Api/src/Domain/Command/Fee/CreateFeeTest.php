<?php

declare(strict_types=1);

/**
 * Create Fee Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Entity\Fee\Fee;

/**
 * Create Fee Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateFeeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
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
            'irfoFeeExempt' => 'Y',
            'waiveReason' => 'testing',
            'quantity' => 2,
            'irhpApplication' => 34,
            'irhpPermitApplication' => 48,
        ];

        $command = CreateFee::create($data);

        $this->assertEquals(111, $command->getApplication());
        $this->assertEquals(222, $command->getLicence());
        $this->assertEquals(333, $command->getTask());
        $this->assertEquals('5.50', $command->getAmount());
        $this->assertEquals('2015-01-01', $command->getInvoicedDate());
        $this->assertEquals(444, $command->getFeeType());
        $this->assertEquals('Some fee', $command->getDescription());
        $this->assertEquals(Fee::STATUS_OUTSTANDING, $command->getFeeStatus());
        $this->assertEquals(555, $command->getBusReg());
        $this->assertEquals(1, $command->getIrfoGvPermit());
        $this->assertEquals(2, $command->getIrfoPsvAuth());
        $this->assertEquals('Y', $command->getIrfoFeeExempt());
        $this->assertEquals('testing', $command->getWaiveReason());
        $this->assertEquals(2, $command->getQuantity());
        $this->assertEquals(34, $command->getIrhpApplication());
        $this->assertEquals(48, $command->getIrhpPermitApplication());
        $this->assertEquals(
            [
                'application' => 111,
                'licence' => 222,
                'task' => 333,
                'amount' => '5.50',
                'invoicedDate' => '2015-01-01',
                'feeType' => 444,
                'description' => 'Some fee',
                'feeStatus' => Fee::STATUS_OUTSTANDING,
                'busReg' => 555,
                'irfoGvPermit' => 1,
                'irfoPsvAuth' => 2,
                'irfoFeeExempt' => 'Y',
                'waiveReason' => 'testing',
                'quantity' => 2,
                'irhpApplication' => 34,
                'irhpPermitApplication' => 48,
            ],
            $command->getArrayCopy()
        );

        $command->exchangeArray(['feeStatus' => 'SomeOtherStatus']);

        $this->assertEquals('SomeOtherStatus', $command->getFeeStatus());
    }
}
