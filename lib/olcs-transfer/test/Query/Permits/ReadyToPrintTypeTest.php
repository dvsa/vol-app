<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintType;

/**
 * ReadyToPrintType Test
 */
class ReadyToPrintTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ReadyToPrintType::create([]);
        static::assertEquals([], $sut->getArrayCopy());
    }
}
