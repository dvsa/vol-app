<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintStock;

/**
 * ReadyToPrintStock Test
 */
final class ReadyToPrintStockTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ReadyToPrintStock::create(
            [
                'irhpPermitType' => 100,
                'country' => 'DE',
            ]
        );
        $this->assertEquals([
            'irhpPermitType' => 100,
            'country' => 'DE',
        ], $sut->getArrayCopy());
    }

    public function testStructureWithoutOptionals()
    {
        $sut = ReadyToPrintStock::create(
            [
                'irhpPermitType' => 100,
            ]
        );
        $this->assertEquals([
            'irhpPermitType' => 100,
            'country' => null,
        ], $sut->getArrayCopy());
    }
}
