<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Organisation;

use Dvsa\Olcs\Transfer\Query\Organisation\Organisation;

/**
 * Organisation test
 */
final class OrganisationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = Organisation::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
    }
}
