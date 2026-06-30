<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintCountry;

/**
 * ReadyToPrintCountry Test
 */
class ReadyToPrintCountryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ReadyToPrintCountry::create(
            [
                'irhpPermitType' => 100,
            ]
        );
        static::assertEquals(
            [
                'irhpPermitType' => 100,
            ],
            $sut->getArrayCopy()
        );
    }
}
