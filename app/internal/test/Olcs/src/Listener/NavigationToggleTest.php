<?php

namespace OlcsTest\Listener;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Listener\NavigationToggle;
use Common\Rbac\IdentityProvider;
use Zend\Navigation\Navigation;
use Zend\Navigation\Page\Uri;
use Zend\Mvc\MvcEvent;
use Common\Rbac\User;
use Mockery as m;

/**
 * Class NavigationToggleTest
 * @package OlcsTest\Listener
 */
class NavigationToggleTest extends TestCase
{
    /** @var \Olcs\Listener\NavigationToggle */
    protected $sut;

    /** @var  Navigation|m\MockInterface */
    private $mockNavigation;

    /** @var  IdentityProvider | m\MockInterface */
    private $mockIdentityProvider;

    public function setUp()
    {
        $this->mockIdentityProvider = m::mock(IdentityProvider::class);
        $this->mockNavigation = m::mock(Navigation::class);

        $this->mockSm = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $this->mockSm
            ->shouldReceive('get')->with('navigation')->andReturn($this->mockNavigation)
            ->shouldReceive('get')->with('Common\Rbac\IdentityProvider')->andReturn($this->mockIdentityProvider);

        $this->sut = new NavigationToggle();
    }

    public function testAttach()
    {
        /** @var \Zend\EventManager\EventManagerInterface | m\MockInterface $mockEventManager */
        $mockEventManager = m::mock(\Zend\EventManager\EventManagerInterface::class);
        $mockEventManager->shouldReceive('attach')->once()
            ->with(MvcEvent::EVENT_DISPATCH, [$this->sut, 'onDispatch'], 20);

        $this->sut->attach($mockEventManager);
    }

    public function testOnDispatch()
    {
        $userData = [ 'disableDataRetentionRecords' => false ];

        $userObject = new User();
        $userObject->setUserData($userData);

        $this->mockIdentityProvider
            ->shouldReceive('getIdentity')
            ->andReturn($userObject);

        $page = new Uri();

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', 'admin-dashboard/admin-data-retention')
            ->andReturn($page);

        /** @var \Zend\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Zend\Mvc\MvcEvent::class);

        $this->sut->createService($this->mockSm);
        $this->sut->onDispatch($mockEvent);

        $isVisible = $this->mockNavigation->findBy('id', 'admin-dashboard/admin-data-retention')->getVisible();

        $this->assertTrue($isVisible);
    }
}
