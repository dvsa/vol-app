<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\Licence\UpdatePeople;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\Licence\UpdatePeople::class)]
final class UpdatePeopleTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'version' => 9999,
            'person' => 'unit_person',
        ];

        $sut = UpdatePeople::create($data);

        $this->assertEquals(9999, $sut->getVersion());
        $this->assertEquals('unit_person', $sut->getPerson());
    }
}
