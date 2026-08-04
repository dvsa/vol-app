<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\OpenWindows;

/**
 * Open Windows test
 */
final class OpenWindowsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = OpenWindows::create(['permitType' => 1]);

        $this->assertEquals(1, $query->getPermitType());
    }
}
