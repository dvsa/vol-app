<?php

namespace OlcsTest\Navigation;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Navigation\RightHandNavigationFactory;

/**
 * Class RightHandNavigationFactoryTest
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class RightHandNavigationFactoryTest extends TestCase
{
    public function testGetName()
    {
        $sut = new RightHandNavigationFactory();

        $this->assertEquals('right-sidebar', $sut->getName());
    }
}
