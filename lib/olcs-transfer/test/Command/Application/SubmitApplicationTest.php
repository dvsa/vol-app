<?php

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication;

/**
 * Submit Application test
 */
class SubmitApplicationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'version' => 222,
        ];

        $command = SubmitApplication::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(222, $command->getVersion());
    }
}
