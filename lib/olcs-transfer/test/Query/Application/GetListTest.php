<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Application;

use Dvsa\Olcs\Transfer\Query\Application\GetList;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Application\GetList::class)]
final class GetListTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $sut = GetList::create(
            [
                'organisation' => 'unit_Org',
                'status' => 'unit_Status',
            ]
        );

        $this->assertEquals('unit_Org', $sut->getOrganisation());
        $this->assertEquals('unit_Status', $sut->getStatus());
    }
}
