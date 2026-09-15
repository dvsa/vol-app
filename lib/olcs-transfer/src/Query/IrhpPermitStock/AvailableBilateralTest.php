<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpPermitStock;

use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\AvailableBilateral;

/**
 * Available Bilateral test
 */
class AvailableBilateralTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'country' => 'NO',

        ];

        $command = AvailableBilateral::create($data);

        $this->assertEquals($data['country'], $command->getCountry());
    }
}
