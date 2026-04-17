<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Formatter;

use Dvsa\Olcs\Api\Service\Publication\Formatter\TransportManagers as Formatter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TransportManagersTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagersTest extends MockeryTestCase
{
    public function setUp(): void
    {
    }

    public function testEmpty(): void
    {
        $this->assertNull(Formatter::format([]));
    }

    public function testTransportManagers(): void
    {
        $tm1 = m::mock(\Dvsa\Olcs\Api\Entity\Tm\TransportManager::class);
        $tm1->shouldReceive('getHomeCd->getPerson->getFullName')->with()->once()->andReturn('Dave Jones');
        $tm2 = m::mock(\Dvsa\Olcs\Api\Entity\Tm\TransportManager::class);
        $tm2->shouldReceive('getHomeCd->getPerson->getFullName')->with()->once()->andReturn('Shirley Basey');

        $this->assertSame('Transport Manager(s): Dave Jones, Shirley Basey', Formatter::format([$tm1, $tm2]));
    }
}
