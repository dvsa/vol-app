<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Variation;

use Dvsa\Olcs\Transfer\Query\Variation\TypeOfLicence;

/**
 * Type of licence test
 */
final class TypeOfLicenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = TypeOfLicence::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
    }
}
