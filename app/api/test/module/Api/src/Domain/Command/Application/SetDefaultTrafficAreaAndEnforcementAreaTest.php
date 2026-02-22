<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\SetDefaultTrafficAreaAndEnforcementArea;

/**
 * SetDefaultTrafficAreaAndEnforcementArea Test
 */
class SetDefaultTrafficAreaAndEnforcementAreaTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $data = [
            'id' => 111,
            'operatingCentre' => 123,
            'postcode' => 'AB1 2CD',
        ];

        $command = SetDefaultTrafficAreaAndEnforcementArea::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(123, $command->getOperatingCentre());
        $this->assertEquals('AB1 2CD', $command->getPostcode());

        $this->assertEquals($data, $command->getArrayCopy());
    }
}
