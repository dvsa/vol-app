<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Messages;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Service\Data\PluginManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Enable;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Laminas\Form\Form;
use Laminas\Mvc\MvcEvent;
use Laminas\Navigation\Navigation;
use Laminas\Router\Http\RouteMatch;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Messages\LicenceEnableDisableMessagingController;
use Olcs\Form\Model\Form\EnableConversations;
use Olcs\Form\Model\Form\EnableConversationsPopup;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Common\Service\Cqrs\Query\CachingQueryService as QueryService;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Http\Response;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Page\AbstractPage;
use Olcs\Event\RouteParam;
use Mockery as m;

/**
 * @property m\LegacyMockInterface|m\MockInterface|LicenceEnableDisableMessagingController $sut
 */
class LicenceEnableDisableMessagingControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->mockScriptFactory = m::mock(ScriptFactory::class);
        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockTableFactory = m::mock(TableFactory::class);
        $this->mockViewHelperManager = m::mock(HelperPluginManager::class);
        $this->mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $this->mockDataServiceManager = m::mock(PluginManager::class);
        $this->mockOppositionHelper = m::mock(OppositionHelperService::class);
        $this->mockComplaintsHelper = m::mock(ComplaintsHelperService::class);
        $this->mockNavigation = m::mock(Navigation::class);
        $this->mockController(
            LicenceEnableDisableMessagingController::class,
            [
                $this->mockScriptFactory,
                $this->mockFormHelper,
                $this->mockTableFactory,
                $this->mockViewHelperManager,
                $this->mockDataServiceManager,
                $this->mockOppositionHelper,
                $this->mockComplaintsHelper,
                $this->mockFlashMessengerHelper,
                $this->mockNavigation,
            ],
        );
    }

    public function testIndexAction(): void
    {
        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockForm = m::mock(Form::class);

        $this->mockScriptFactory->shouldReceive('loadFiles')
                                ->once()
                                ->with(['table-actions']);

        $mockMvcEvent->shouldReceive('getRouteMatch')
                     ->once()
                     ->andReturn($mockRouteMatch);

        $this->sut->shouldReceive('getEvent')
                  ->once()
                  ->andReturn($mockMvcEvent);

        $mockRouteMatch->shouldReceive('getParam')
                       ->once()
                       ->with('type', null)
                       ->andReturn('enable');

        $this->mockFormHelper->shouldReceive('createForm')
                             ->once()
                             ->with(EnableConversations::class)
                             ->andReturn($mockForm);
        $this->mockFormHelper->shouldReceive('setFormActionFromRequest')
                             ->once()
                             ->with($mockForm, $this->request);
        $this->mockFormHelper->shouldReceive('processAddressLookupForm')
                             ->once()
                             ->with($mockForm, $this->request);

        $this->sut
            ->shouldReceive('render')
            ->once()
            ->withArgs(
                function ($view, $title) {
                    $this->assertInstanceOf(ViewModel::class, $view);
                    $this->assertEquals('Enable messaging', $title);
                    return true;
                },
            );

        $this->sut->indexAction();
    }

    public function testPopupAction(): void
    {
        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockHandleQuery = m::mock(HandleQuery::class);
        $mockHandleCommand = m::mock(HandleCommand::class);
        $mockQueryResponse = m::mock(Response::class);
        $mockForm = m::mock(Form::class);
        $mockCommandResponse = m::mock(Response::class);
        $mockRedirectPlugin = m::mock(Redirect::class);

        $mockMvcEvent->shouldReceive('getRouteMatch')
                     ->times(3)
                     ->andReturn($mockRouteMatch);

        $this->sut->shouldReceive('getEvent')
                  ->times(3)
                  ->andReturn($mockMvcEvent);

        $mockRouteMatch->shouldReceive('getParam')
                       ->once()
                       ->with('licence', null)
                       ->andReturn('7');
        $mockRouteMatch->shouldReceive('getParam')
                       ->once()
                       ->with('type', null)
                       ->andReturn('enable');

        $this->sut->shouldReceive('plugin')
                  ->once()
                  ->with('handleQuery')
                  ->andReturn($mockHandleQuery);
        $this->sut->shouldReceive('plugin')
                  ->once()
                  ->with('handleCommand')
                  ->andReturn($mockHandleCommand);
        $this->sut->shouldReceive('plugin')
                  ->once()
                  ->with('redirect')
                  ->andReturn($mockRedirectPlugin);

        $mockHandleQuery->shouldReceive('__invoke')
                        ->once()
                        ->withArgs(
                            function ($licence) {
                                $this->assertInstanceOf(Licence::class, $licence);
                                $this->assertEquals(7, $licence->getId());

                                return true;
                            },
                        )->andReturn($mockQueryResponse);

        $mockQueryResponse->shouldReceive('isOk')
                          ->once()
                          ->andReturnTrue();
        $mockQueryResponse->shouldReceive('getResult')
                          ->once()
                          ->andReturn(
                              [
                                  'organisation' => [
                                      'id' => 7,
                                  ],
                              ],
                          );

        $this->mockFormHelper->shouldReceive('createForm')
                             ->once()
                             ->with(EnableConversationsPopup::class)
                             ->andReturn($mockForm);
        $this->mockFormHelper->shouldReceive('setFormActionFromRequest')
                             ->once()
                             ->with($mockForm, $this->request);
        $this->mockFormHelper->shouldReceive('processAddressLookupForm')
                             ->once()
                             ->with($mockForm, $this->request);

        $this->request->shouldReceive('isPost')
                      ->andReturn(true);

        $mockHandleCommand
            ->shouldReceive('__invoke')
            ->once()
            ->withArgs(
                function ($command) {
                    $this->assertInstanceOf(Enable::class, $command);
                    $this->assertEquals(7, $command->getOrganisation());

                    return true;
                },
            )
            ->andReturn($mockCommandResponse);

        $mockCommandResponse->shouldReceive('isOk')
                            ->once()
                            ->andReturnTrue();

        $this->mockFlashMessengerHelper->shouldReceive('addSuccessMessage')
                                       ->once()
                                       ->with('messaging-enabled-success');

        $mockRouteMatch->shouldReceive('getParams')
                       ->once()
                       ->andReturn();

        $mockRedirectPlugin->shouldReceive('toRouteAjax')
                           ->once();

        $this->sut->popupAction();
    }
}
