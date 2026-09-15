<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Variation;

use Dvsa\Olcs\Transfer\Query\Variation\Variation;

/**
 * Variation test
 */
final class VariationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = Variation::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
    }
}
