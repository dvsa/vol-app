<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\Terminate;

/**
 * Terminate test
 */
class TerminateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = ['id' => 127];

        $command = Terminate::create($data);

        $this->assertEquals(127, $command->getId());
    }
}
