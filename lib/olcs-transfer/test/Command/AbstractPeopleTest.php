<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command;

use Dvsa\Olcs\Transfer\Command\AbstractPeople;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\AbstractPeople::class)]
final class AbstractPeopleTest extends MockeryTestCase
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

        $this->assertEquals(111, $sut->getId());
        $this->assertEquals('unit_title', $sut->getTitle());
        $this->assertEquals('unit_forename', $sut->getForename());
        $this->assertEquals('unit_familyName', $sut->getFamilyName());
        $this->assertEquals('unit_otherName', $sut->getOtherName());
        $this->assertEquals('unit_birthDate', $sut->getBirthDate());
        $this->assertEquals('unit_position', $sut->getPosition());
    }
}
