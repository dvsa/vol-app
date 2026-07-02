<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpPermit;

use Dvsa\Olcs\Transfer\Command\IrhpPermit\Terminate;

/**
 * Set IRHP Permit status to terminated test
 */
class TerminateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [ 'id' => 222 ];
        $command = Terminate::create($data);
        $this->assertEquals(222, $command->getId());
    }
}
