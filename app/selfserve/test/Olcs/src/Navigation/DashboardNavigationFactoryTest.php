<?php

namespace OlcsTest\Navigation;

use PHPUnit_Framework_TestCase;

use Olcs\Navigation\DashboardNavigationFactory;

/**
 * Class DashboardNavigationFactoryTest
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class DashboardNavigationFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $sut = new DashboardNavigationFactory;

        $this->assertEquals('dashboard', $sut->getName());
    }
}
