<?php

namespace Dvsa\OlcsTest\Transfer\Query\Organisation;

use Dvsa\Olcs\Transfer\Query\Application\Declaration;

/**
 * Declaration Test
 */
class DeclarationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = Declaration::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
    }
}
