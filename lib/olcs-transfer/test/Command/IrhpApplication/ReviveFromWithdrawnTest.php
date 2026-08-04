<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\ReviveFromWithdrawn;

/**
 * Revive from withdrawn test
 */
final class ReviveFromWithdrawnTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = ['id' => 114];

        $command = ReviveFromWithdrawn::create($data);

        $this->assertEquals(114, $command->getId());
    }
}
