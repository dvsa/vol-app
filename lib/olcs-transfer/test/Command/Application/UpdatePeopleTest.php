<?php

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdatePeople;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Command\Application\UpdatePeople
 */
class UpdatePeopleTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'version' => 9999,
            'person' => 'unit_person',
            'birthDate' => 'unit_birthDate',
        ];

        $sut = UpdatePeople::create($data);

        static::assertEquals(9999, $sut->getVersion());
        static::assertEquals('unit_person', $sut->getPerson());
        static::assertEquals('unit_birthDate', $sut->getBirthDate());
    }
}
