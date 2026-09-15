<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Query\Licence\PsvVehiclesExport;

/**
 * PSV export vehicles test
 */
final class PsvVehiclesExportTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = PsvVehiclesExport::create(['id' => 111, 'includeRemoved' => true]);

        $this->assertEquals(111, $command->getId());
        $this->assertTrue($command->getIncludeRemoved());
    }
}
