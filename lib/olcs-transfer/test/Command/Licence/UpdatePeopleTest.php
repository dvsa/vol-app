<?php

namespace Dvsa\OlcsTest\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\Licence\UpdatePeople;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers UpdatePeople
 */
class UpdatePeopleTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'version' => 9999,
            'person' => 'unit_person',
        ];

        $sut = UpdatePeople::create($data);

        static::assertEquals(9999, $sut->getVersion());
        static::assertEquals('unit_person', $sut->getPerson());
    }
}
