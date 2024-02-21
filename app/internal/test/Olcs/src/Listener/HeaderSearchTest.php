<?php

namespace OlcsTest\Listener;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Common\Rbac\User;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Form\Element\SearchDateRangeFieldset;
use Olcs\Form\Element\SearchFilterFieldset;
use Olcs\Form\Element\SearchOrderFieldset;
use Olcs\Form\Model\Form;
use Olcs\Listener\HeaderSearch;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\Placeholder;
use LmcRbacMvc\Identity\IdentityProviderInterface;

class HeaderSearchTest extends TestCase
{
    /** @var \Olcs\Listener\HeaderSearch */
    protected $sut;

    /** @var  m\MockInterface */
    private $mockFormHlp;
    private $mockSm;
    /** @var  \Common\Service\Data\Search\Search | m\MockInterface  */
    private $mockSearchSrv;
    /** @var  \Laminas\Form\FormElementManager | m\MockInterface  */
    private $mockFormElmMngr;
    /** @var  \Laminas\View\HelperPluginManager | m\MockInterface  */
    private $mockViewHlprMngr;
    /** @var  IdentityProviderInterface | m\MockInterface  */
    private $mockAuthService;

    public function setUp(): void
    {
        $this->mockFormHlp = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->mockSearchSrv = m::mock(\Common\Service\Data\Search\Search::class);
        $this->mockFormElmMngr = m::mock(\Laminas\Form\FormElementManager::class);
        $this->mockViewHlprMngr = m::mock(\Laminas\View\HelperPluginManager::class);
        $this->mockAuthService = m::mock(IdentityProviderInterface::class);
        $this->mockTransHelper = m::mock(TranslationHelperService::class);

        $this->mockSm = m::mock(ContainerInterface::class);
        $this->mockSm
            ->shouldReceive('get')->with('DataServiceManager')->andReturnSelf()
            ->shouldReceive('get')->with(FormHelperService::class)->andReturn($this->mockFormHlp)
            ->shouldReceive('get')->with(\Common\Service\Data\Search\Search::class)->andReturn($this->mockSearchSrv)
            ->shouldReceive('get')->with('FormElementManager')->andReturn($this->mockFormElmMngr)
            ->shouldReceive('get')->with(TranslationHelperService::class)->andReturn($this->mockTransHelper)
            ->shouldReceive('get')->with(IdentityProviderInterface::class)->andReturn($this->mockAuthService)
            ->shouldReceive('get')->with('ViewHelperManager')->andReturn($this->mockViewHlprMngr);

        $this->sut = new HeaderSearch();
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
     * @dataProvider dpOnDispatch
     */
    public function testOnDispatch($userData, $setTimes)
    {
        $index = 'licence';

        $params = ['test' => 'value'];

        $mockForm = new \Laminas\Form\Form();

        $this->mockSearchSrv
            ->shouldReceive('setIndex')->with($index)
            ->shouldReceive('getFilters')->with([]);

        $sff = new SearchFilterFieldset();
        $sff->setName('filter');
        $sff->setSearchService($this->mockSearchSrv);
        $this->mockFormElmMngr->shouldReceive('get')
            ->with('SearchFilterFieldset', ['index' => $index, 'name' => 'filter'])
            ->andReturn($sff);

        $srf = new SearchDateRangeFieldset();
        $srf->setName('dateRanges');
        $srf->setSearchService($this->mockSearchSrv);
        $this->mockFormElmMngr->shouldReceive('get')
            ->with('SearchDateRangeFieldset', ['index' => $index, 'name' => 'dateRanges'])
            ->andReturn($srf);

        $sof = new SearchOrderFieldset();
        $sof->setName('sort');
        $sof->setSearchService($this->mockSearchSrv);
        $this->mockFormElmMngr->shouldReceive('get')
            ->with(SearchOrderFieldset::class, ['index' => $index, 'name' => 'sort'])
            ->andReturn($sof);

        $mockHsForm = \Mockery::mock();

        $this->mockFormHlp
            ->shouldReceive('createForm')->with(Form\HeaderSearch::class, false)->andReturn($mockHsForm)
            ->shouldReceive('createForm')->with(Form\SearchFilter::class, false)->andReturn($mockForm);

        $userObject = new User();
        $userObject->setUserData($userData);

        $this->mockAuthService->shouldReceive('getIdentity')
            ->andReturn($userObject);

        $mockElement = \Mockery::mock();
        $mockHsForm->shouldReceive('get')->andReturn($mockElement);
        $mockHsForm->shouldReceive('bind')->once();

        $mockElement->shouldReceive('setValueOptions')->times($setTimes)->withAnyArgs()->andReturn();

        $placeholder = new Placeholder();
        $placeholder->getContainer('headerSearch')->set('foobar');

        $this->mockViewHlprMngr->shouldReceive('get')->with('placeholder')->andReturn($placeholder);

        /** @var \Laminas\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRouteMatch')->andReturnSelf();
        $mockEvent->shouldReceive('getParams')->andReturn($params);
        $mockEvent->shouldReceive('getParam')->with('index')->andReturn($index);

        $this->sut->__invoke($this->mockSm, HeaderSearch::class);
        $this->sut->onDispatch($mockEvent);
    }

    public function dpOnDispatch(): array
    {
        return [
            'loggedin' => [
                [
                    'id' => 'usr123',
                    'dataAccess' => [
                        'allowedSearchIndexes' => [
                            'licence' => 'licence'
                        ]
                    ]
                ],
                1
            ],
            'notloggedin' => [
                [],
                0
            ]
        ];
    }

    public function testInvoke()
    {
        $service = $this->sut->__invoke($this->mockSm, HeaderSearch::class);

        $this->assertSame($this->sut, $service);
        $this->assertSame($this->mockViewHlprMngr, $this->sut->getViewHelperManager());
        $this->assertSame($this->mockSearchSrv, $this->sut->getSearchService());
        $this->assertSame($this->mockFormElmMngr, $this->sut->getFormElementManager());
    }

    public function testGetViewHelperManager()
    {
        $this->sut->setViewHelperManager('foo');
        $this->assertEquals('foo', $this->sut->getViewHelperManager());
    }

    public function testSetViewHelperManager()
    {
        $this->assertSame($this->sut->setViewHelperManager('foo'), $this->sut);
        $this->assertEquals('foo', $this->sut->getViewHelperManager());
    }
}
