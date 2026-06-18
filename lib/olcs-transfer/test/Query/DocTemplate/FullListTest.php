<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\Query\DocTemplate\FullList;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\DocTemplate\FullList
 */


class FullListTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = FullList::create(
            [
                'category' => 11,
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
            ]
        );
        static::assertEquals(
            [
                'category' => 11,
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
                'sortWhitelist' => []
            ],
            $sut->getArrayCopy()
        );
    }
}
