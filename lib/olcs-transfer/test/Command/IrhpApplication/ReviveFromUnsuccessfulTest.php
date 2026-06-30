<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\ReviveFromUnsuccessful;

/**
 * Revive from unsuccessful test
 */
class ReviveFromUnsuccessfulTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = ['id' => 116];

        $command = ReviveFromUnsuccessful::create($data);

        $this->assertEquals(116, $command->getId());
    }
}
