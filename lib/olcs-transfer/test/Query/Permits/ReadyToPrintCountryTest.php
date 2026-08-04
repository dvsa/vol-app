<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintCountry;

/**
 * ReadyToPrintCountry Test
 */
final class ReadyToPrintCountryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ReadyToPrintCountry::create(
            [
                'irhpPermitType' => 100,
            ]
        );
        $this->assertEquals([
            'irhpPermitType' => 100,
        ], $sut->getArrayCopy());
    }
}
