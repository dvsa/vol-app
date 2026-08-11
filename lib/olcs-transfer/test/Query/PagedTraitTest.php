<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Variation;

use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversTrait(\Dvsa\Olcs\Transfer\Query\PagedTrait::class)]
final class PagedTraitTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $sut = new class {
            use PagedTrait;
        };

        //  check limit
        $actual = $sut->setLimit(1234);
        $this->assertEquals(1234, $sut->getLimit());

        //  check page
        $actual = $sut->setPage(9999);
        $this->assertEquals(9999, $sut->getPage());
    }
}
