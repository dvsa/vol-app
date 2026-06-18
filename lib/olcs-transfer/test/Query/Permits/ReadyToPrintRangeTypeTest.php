<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintRangeType;

/**
 * ReadyToPrintRangeType Test
 */
class ReadyToPrintRangeTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $irhpPermitStock = 123;

        $sut = ReadyToPrintRangeType::create(
            [
                'irhpPermitStock' => $irhpPermitStock,
            ]
        );
        static::assertEquals($irhpPermitStock, $sut->getIrhpPermitStock());
        static::assertEquals(
            [
                'irhpPermitStock' => $irhpPermitStock,
            ],
            $sut->getArrayCopy()
        );
    }
}
