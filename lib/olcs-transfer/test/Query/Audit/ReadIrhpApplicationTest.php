<?php

namespace Dvsa\OlcsTest\Transfer\Query\Audit;

use Dvsa\Olcs\Transfer\Query\Audit\ReadIrhpApplication;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\Audit\ReadIrhpApplication
 */
class ReadIrhpApplicationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ReadIrhpApplication::create(
            [
                'id' => 2,
                'page' => 1,
                'limit' => 10,
            ]
        );
        static::assertEquals(
            [
                'id' => 2,
                'page' => 1,
                'limit' => 10,
            ],
            $sut->getArrayCopy()
        );
    }
}
