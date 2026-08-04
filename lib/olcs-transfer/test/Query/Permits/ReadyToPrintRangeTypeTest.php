<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintRangeType;

/**
 * ReadyToPrintRangeType Test
 */
final class ReadyToPrintRangeTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $irhpPermitStock = 123;

        $sut = ReadyToPrintRangeType::create(
            [
                'irhpPermitStock' => $irhpPermitStock,
            ]
        );
        $this->assertEquals($irhpPermitStock, $sut->getIrhpPermitStock());
        $this->assertEquals([
            'irhpPermitStock' => $irhpPermitStock,
        ], $sut->getArrayCopy());
    }
}
