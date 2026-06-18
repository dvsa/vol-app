<?php

namespace Dvsa\OlcsTest\Transfer\Query\Processing;

use Dvsa\Olcs\Transfer\Query\Processing\History;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\Processing\History
 */
class HistoryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = History::create(
            [
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
                'irhpApplication' => 100,
                'case' => 200,
                'licence' => 300,
                'organisation' => 400,
                'transportManager' => 500,
                'user' => 600,
                'application' => 700,
            ]
        );
        static::assertEquals(
            [
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
                'sortWhitelist' => [],
                'irhpApplication' => 100,
                'case' => 200,
                'licence' => 300,
                'organisation' => 400,
                'transportManager' => 500,
                'user' => 600,
                'application' => 700,
            ],
            $sut->getArrayCopy()
        );
    }
}
