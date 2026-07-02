<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\Query\IrhpPermit\ById;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpPermit\ById
 */


class ByIdTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ById::create(
            [
              'id' => 2
            ]
        );
        static::assertEquals(
            [
            'id' => 2
            ],
            $sut->getArrayCopy()
        );
    }
}
