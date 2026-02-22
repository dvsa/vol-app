<?php

declare(strict_types=1);

namespace OlcsTest\Navigation;

use PHPUnit_Framework_TestCase;
use Olcs\Navigation\DashboardNavigationFactory;

/**
 * Class DashboardNavigationFactoryTest
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class DashboardNavigationFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetName(): void
    {
        $sut = new DashboardNavigationFactory();

        $this->assertEquals('dashboard', $sut->getName());
    }
}
