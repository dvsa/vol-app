<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetList;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\IrhpPermit\GetList::class)]
final class GetListTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = GetList::create(
            [
              'irhpPermitApplication' => 2,
              'page' => 1,
              'limit' => 10,
              'sort' => 'id',
              'order' => 'ASC',
            ]
        );
        $this->assertEquals([
        'irhpPermitApplication' => 2,
        'page' => 1,
        'limit' => 10,
        'sort' => 'id',
        'order' => 'ASC',
        'sortWhitelist' => []
        ], $sut->getArrayCopy());
    }
}
