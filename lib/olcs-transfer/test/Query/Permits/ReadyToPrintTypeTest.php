<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintType;

/**
 * ReadyToPrintType Test
 */
final class ReadyToPrintTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ReadyToPrintType::create([]);
        $this->assertEquals([], $sut->getArrayCopy());
    }
}
