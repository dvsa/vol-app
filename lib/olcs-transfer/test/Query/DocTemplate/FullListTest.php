<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\Query\DocTemplate\FullList;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\DocTemplate\FullList::class)]
final class FullListTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals([
            'category' => 11,
            'page' => 1,
            'limit' => 10,
            'sort' => 'id',
            'order' => 'ASC',
            'sortWhitelist' => []
        ], $sut->getArrayCopy());
    }
}
