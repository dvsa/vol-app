<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\ById;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\ById
 */


class ByIdTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ById::create(
            [
              'id' => 2,
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
