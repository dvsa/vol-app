<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintStock;

/**
 * ReadyToPrintStock Test
 */
class ReadyToPrintStockTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ReadyToPrintStock::create(
            [
                'irhpPermitType' => 100,
                'country' => 'DE',
            ]
        );
        static::assertEquals(
            [
                'irhpPermitType' => 100,
                'country' => 'DE',
            ],
            $sut->getArrayCopy()
        );
    }

    public function testStructureWithoutOptionals()
    {
        $sut = ReadyToPrintStock::create(
            [
                'irhpPermitType' => 100,
            ]
        );
        static::assertEquals(
            [
                'irhpPermitType' => 100,
                'country' => null,
            ],
            $sut->getArrayCopy()
        );
    }
}
