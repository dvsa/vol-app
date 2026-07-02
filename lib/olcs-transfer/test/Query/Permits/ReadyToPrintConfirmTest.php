<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintConfirm;

/**
 * ReadyToPrintConfirm Test
 */
class ReadyToPrintConfirmTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ReadyToPrintConfirm::create(
            [
                'ids' => [1, 2, 3],
            ]
        );
        $this->assertEquals([1, 2, 3], $sut->getIds());
        $this->assertEquals(
            ['ids' => [1, 2, 3]],
            $sut->getArrayCopy()
        );
    }
}
