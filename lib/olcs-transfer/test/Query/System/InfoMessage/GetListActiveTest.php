<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Application;

use Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetListActive;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetListActive::class)]
final class GetListActiveTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = GetListActive::create(['isInternal' => true]);

        $this->assertEquals(true, $query->isInternal());
    }
}
