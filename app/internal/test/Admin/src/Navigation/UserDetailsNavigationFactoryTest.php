<?php

/**
 * Test UserDetailsNavigationFactory
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace AdminTest\Navigation;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Admin\Navigation\UserDetailsNavigationFactory;

/**
 * Test UserDetailsNavigationFactory
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class UserDetailsNavigationFactoryTest extends MockeryTestCase
{
    public function testGetName()
    {
        $sut = new UserDetailsNavigationFactory();

        $this->assertEquals('user-details', $sut->getName());
    }
}
