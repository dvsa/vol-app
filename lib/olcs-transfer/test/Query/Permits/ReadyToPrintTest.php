<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrint;

/**
 * ReadyToPrint Test
 */
class ReadyToPrintTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $irhpPermitRangeType = 'range.type';

        $sut = ReadyToPrint::create(
            [
                'irhpPermitStock' => 100,
                'irhpPermitRangeType' => $irhpPermitRangeType,
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
            ]
        );
        static::assertEquals($irhpPermitRangeType, $sut->getIrhpPermitRangeType());
        static::assertEquals(
            [
                'irhpPermitStock' => 100,
                'irhpPermitRangeType' => $irhpPermitRangeType,
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
                'sortWhitelist' => []
            ],
            $sut->getArrayCopy()
        );
    }

    public function testStructureWithoutOptionals()
    {
        $sut = ReadyToPrint::create(
            [
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
            ]
        );
        static::assertNull($sut->getIrhpPermitRangeType());
        static::assertEquals(
            [
                'irhpPermitStock' => null,
                'irhpPermitRangeType' => null,
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
                'sortWhitelist' => []
            ],
            $sut->getArrayCopy()
        );
    }
}
