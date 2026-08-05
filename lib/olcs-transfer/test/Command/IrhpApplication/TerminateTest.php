<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\Terminate;

/**
 * Terminate test
 */
final class TerminateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = ['id' => 127];

        $command = Terminate::create($data);

        $this->assertEquals(127, $command->getId());
    }
}
