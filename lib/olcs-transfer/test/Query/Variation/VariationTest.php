<?php

namespace Dvsa\OlcsTest\Transfer\Query\Variation;

use Dvsa\Olcs\Transfer\Query\Variation\Variation;

/**
 * Variation test
 */
class VariationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = Variation::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
    }
}
