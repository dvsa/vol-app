<?php

namespace Dvsa\OlcsTest\Transfer\Query\Fee;

use Dvsa\Olcs\Transfer\Query\Fee\FeeTypeList;

/**
 * Fee List Test
 */
class FeeTypeListTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = FeeTypeList::create(
            [
                'application' => 11,
                'licence' => 12,
                'busReg' => 13,
                'organisation' => 14,
                'isMiscellaneous' => 1,
                'effectiveDate' => '2015-10-23',
                'currentFeeType' => 123
            ]
        );

        $this->assertEquals(123, $query->getCurrentFeeType());

        $this->assertEquals(
            [
                'application' => 11,
                'licence' => 12,
                'busReg' => 13,
                'organisation' => 14,
                'isMiscellaneous' => 1,
                'effectiveDate' => '2015-10-23',
                'currentFeeType' => 123
            ],
            $query->getArrayCopy()
        );
    }
}
