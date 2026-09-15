<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\AvailableYears;

/**
 * Available Years test
 */
final class AvailableYearsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = AvailableYears::create(['type' => 1]);

        $this->assertEquals(1, $query->getType());
    }
}
