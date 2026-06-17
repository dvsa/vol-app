<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpApplication\ById
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
