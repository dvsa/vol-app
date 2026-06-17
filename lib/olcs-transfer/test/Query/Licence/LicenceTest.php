<?php

namespace Dvsa\OlcsTest\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Query\Licence\Licence;

/**
 * Licence test
 */
class LicenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = Licence::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
    }
}
