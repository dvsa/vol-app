<?php

namespace Dvsa\OlcsTest\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\Licence\PrintLicence;

/**
 * @covers \Dvsa\Olcs\Transfer\Command\Licence\PrintLicence
 */
class PrintLicenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111
        ];

        $command = PrintLicence::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(true, $command->getDispatch());
    }

    public function testStructureDispatch()
    {
        $data = [
            'id' => 111,
            'dispatch' => false
        ];

        $command = PrintLicence::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(false, $command->getDispatch());
    }
}
