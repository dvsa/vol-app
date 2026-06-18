<?php

namespace Dvsa\OlcsTest\Transfer\Command;

use Dvsa\Olcs\Transfer\Command\AbstractPeople;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Command\AbstractPeople
 */
class AbstractPeopleTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'id' => 111,
            'title' => 'unit_title',
            'forename' => 'unit_forename',
            'familyName' => 'unit_familyName',
            'otherName' => 'unit_otherName',
            'birthDate' => 'unit_birthDate',
            'position' => 'unit_position',
        ];

        /** @var AbstractPeople | m\MockInterface $sut */
        $sut = m::mock(AbstractPeople::class)->makePartial();
        $sut->exchangeArray($data);

        static::assertEquals(111, $sut->getId());
        static::assertEquals('unit_title', $sut->getTitle());
        static::assertEquals('unit_forename', $sut->getForename());
        static::assertEquals('unit_familyName', $sut->getFamilyName());
        static::assertEquals('unit_otherName', $sut->getOtherName());
        static::assertEquals('unit_birthDate', $sut->getBirthDate());
        static::assertEquals('unit_position', $sut->getPosition());
    }
}
