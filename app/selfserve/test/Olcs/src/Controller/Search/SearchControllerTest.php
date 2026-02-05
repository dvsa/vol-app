<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Search;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\FormElementManager;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\Search\SearchController as Sut;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\Plugin\Redirect;

/**
 * Class SearchControllerTest
 */
class SearchControllerTest extends TestCase
{
    /** @var Sut */
    protected $sut;

    /** @var m\MockInterface */
    protected $authService;

    public function setUp(): void
    {
        $niTextTranslationUtil = m::mock(NiTextTranslation::class);
        $this->authService = m::mock(AuthorizationService::class);
        $scriptFactory = m::mock(ScriptFactory::class);
        $formHelper = m::mock(FormHelperService::class);
        $navigation = m::mock();
        $formElementManager = m::mock(FormElementManager::class);
        $viewHelperManager = m::mock();
        $dataServiceManager = m::mock();
        $translationHelper = m::mock(TranslationHelperService::class);

        $this->sut = m::mock(Sut::class, [
            $niTextTranslationUtil,
            $this->authService,
            $scriptFactory,
            $formHelper,
            $navigation,
            $formElementManager,
            $viewHelperManager,
            $dataServiceManager,
            $translationHelper
        ])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testIndexActionWithoutIndex(): void
    {
        $params = m::mock();
        $params->shouldReceive('fromRoute')
            ->with('index')
            ->once()
            ->andReturn(null);

        $this->sut->shouldReceive('params')->andReturn($params);

        $view = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $view);
        $this->assertEquals('search/index', $view->getTemplate());
    }

    public function testIndexActionRedirectsWhenNotAuthorizedForVehicleExternal(): void
    {
        $params = m::mock();
        $params->shouldReceive('fromRoute')
            ->with('index')
            ->once()
            ->andReturn('vehicle-external');

        $this->sut->shouldReceive('params')->andReturn($params);

        $this->authService->shouldReceive('isGranted')
            ->with('selfserve-search-vehicle-external')
            ->once()
            ->andReturn(false);

        $redirectMock = m::mock(Redirect::class);
        $redirectMock->shouldReceive('toRoute')
            ->with('auth/login/GET')
            ->once()
            ->andReturn('redirectResponse');

        $this->sut->shouldReceive('redirect')->andReturn($redirectMock);

        $result = $this->sut->indexAction();

        $this->assertEquals('redirectResponse', $result);
    }
}
