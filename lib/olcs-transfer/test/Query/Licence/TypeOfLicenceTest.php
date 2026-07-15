<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Query\Licence\TypeOfLicence;

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
