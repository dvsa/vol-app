<?php

namespace Dvsa\OlcsTest\Transfer\Query\Application;

use Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetListActive;

/**
 * @covers Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetListActive
 */
class GetListActiveTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = GetListActive::create(['isInternal' => true]);

        static::assertEquals(true, $query->isInternal());
    }
}
