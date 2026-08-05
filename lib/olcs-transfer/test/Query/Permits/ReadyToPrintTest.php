<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrint;

/**
 * ReadyToPrint Test
 */
final class ReadyToPrintTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals($irhpPermitRangeType, $sut->getIrhpPermitRangeType());
        $this->assertEquals([
            'irhpPermitStock' => 100,
            'irhpPermitRangeType' => $irhpPermitRangeType,
            'page' => 1,
            'limit' => 10,
            'sort' => 'id',
            'order' => 'ASC',
            'sortWhitelist' => []
        ], $sut->getArrayCopy());
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
        $this->assertNull($sut->getIrhpPermitRangeType());
        $this->assertEquals([
            'irhpPermitStock' => null,
            'irhpPermitRangeType' => null,
            'page' => 1,
            'limit' => 10,
            'sort' => 'id',
            'order' => 'ASC',
            'sortWhitelist' => []
        ], $sut->getArrayCopy());
    }
}
