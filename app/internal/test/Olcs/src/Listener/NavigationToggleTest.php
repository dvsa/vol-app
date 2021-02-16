<?php

namespace OlcsTest\Listener;

use Common\Rbac\IdentityProvider;
use Common\Rbac\User;
use Common\RefData;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Laminas\Mvc\MvcEvent;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\AbstractPage;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Listener\NavigationToggle;

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

    /** @var  QuerySender|m\MockInterface */
    private $mockQuerySender;

    /** @var  IdentityProvider | m\MockInterface */
    private $mockIdentityProvider;

    public function setUp(): void
    {
        $this->mockIdentityProvider = m::mock(IdentityProvider::class);
        $this->mockNavigation = m::mock(Navigation::class);
        $this->mockQuerySender = m::mock(QuerySender::class);

        $this->mockSm = m::mock(\Laminas\ServiceManager\ServiceLocatorInterface::class);
        $this->mockSm
            ->shouldReceive('get')->with('navigation')->andReturn($this->mockNavigation)
            ->shouldReceive('get')->with('QuerySender')->andReturn($this->mockQuerySender)
            ->shouldReceive('get')->with('Common\Rbac\IdentityProvider')->andReturn($this->mockIdentityProvider);

        $this->sut = new NavigationToggle();
    }

    public function testAttach()
    {
        /** @var \Laminas\EventManager\EventManagerInterface | m\MockInterface $mockEventManager */
        $mockEventManager = m::mock(\Laminas\EventManager\EventManagerInterface::class);
        $mockEventManager->shouldReceive('attach')->once()
            ->with(MvcEvent::EVENT_DISPATCH, [$this->sut, 'onDispatch'], 20);

        $this->sut->attach($mockEventManager);
    }

    /**
     * Tests feature toggles aren't checked when not logged in
     */
    public function testOnDispatchNotLoggedIn()
    {
        $userData = [];

        $userObject = new User();
        $userObject->setUserData($userData);

        $this->mockIdentityProvider
            ->shouldReceive('getIdentity')
            ->andReturn($userObject);

        /** @var MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(MvcEvent::class);

        $this->sut->createService($this->mockSm);
        $this->sut->onDispatch($mockEvent);
        $mockEvent->shouldNotReceive('getRouteMatch->getParams');
    }

    /**
     * @dataProvider dpOnDispatch
     */
    public function testOnDispatch($routeParams, $licenceQueryResult, $expectedIrhpPermitsVisible)
    {
        $userData = [
            'id' => 'usr123'
        ];

        $userObject = m::mock(User::class);
        $userObject->shouldReceive('getUserData')
            ->withNoArgs()
            ->andReturn($userData);

        $this->mockIdentityProvider->shouldReceive('getIdentity')
            ->withNoArgs()
            ->andReturn($userObject);

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('getResult')
            ->withNoArgs()
            ->andReturn($licenceQueryResult);

        $licenceId = $routeParams['licence'] ?? null;
        $this->mockQuerySender->shouldReceive('send')
            ->with(m::type(Licence::class))
            ->andReturnUsing(function ($query) use ($licenceId, $mockLicence) {
                $this->assertEquals($licenceId, $query->getId());
                return $mockLicence;
            });

        $mockIrhpPermitsPage = m::mock(AbstractPage::class);
        $mockIrhpPermitsPage->shouldReceive('setVisible')
            ->with($expectedIrhpPermitsVisible)
            ->once();

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', 'licence_irhp_permits')
            ->andReturn($mockIrhpPermitsPage);

        /** @var MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(MvcEvent::class);
        $mockEvent->shouldReceive('getRouteMatch->getParams')
            ->withNoArgs()
            ->andReturn($routeParams);

        $this->sut->createService($this->mockSm);
        $this->sut->onDispatch($mockEvent);
    }

    public function dpOnDispatch(): array
    {
        return [
            'goods' => [
                ['licence' => 7],
                ['goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE]],
                true
            ],
            'psv' => [
                ['licence' => 7],
                ['goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_PSV]],
                false
            ],
            'no licence' => [
                [],
                null,
                false
            ],
        ];
    }
}
