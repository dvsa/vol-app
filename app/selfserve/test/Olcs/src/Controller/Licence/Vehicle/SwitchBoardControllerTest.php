<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Form\Form;
use Common\Form\FormValidator;
use Common\Service\Cqrs\Response as QueryResponse;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Common\View\Helper\Panel;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Hamcrest\Core\IsInstanceOf;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\MockInterface;
use Olcs\Controller\Licence\Vehicle\ListVehicleController;
use Olcs\Controller\Licence\Vehicle\SwitchBoardController;
use Olcs\Controller\Licence\Vehicle\SwitchBoardControllerFactory;
use Olcs\Form\Model\Form\Vehicle\SwitchBoard;
use Olcs\Session\LicenceVehicleManagement;

class SwitchBoardControllerTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected const VEHICLES_ROUTE = ['lva-licence/vehicles', [], [], true];
    protected const A_DECISION_VALUE = 'A_DECISION_VALUE';


    /**
     * @test
     */
    public function indexAction_IsCallable()
    {
        // Setup

        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'indexAction']);
    }

    /**
     * @test
     * @depends indexAction_IsCallable
     */
    public function indexAction_ReturnsViewModel()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithPanel_WhenFlashMessageHasPanelNamespace()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $flashMessenger = $this->resolveMockService($this->serviceManager, FlashMessenger::class);
        $flashMessenger->shouldReceive('getMessages')
            ->with('panel')
            ->andReturn(['title']);

        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);

        $expected = [
            'title' => 'title',
            'theme' => Panel::TYPE_SUCCESS
        ];

        // Assert
        $this->assertSame($expected, $result->getVariable('panel'));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel_WithPanel_WhenFlashMessageHasPanelNamespace
     */
    public function indexAction_ReturnsViewModel_WithPanelBody_WhenFlashMessageHasPanelNamespaceSecondMessage()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $flashMessenger = $this->resolveMockService($this->serviceManager, FlashMessenger::class);
        $flashMessenger->shouldReceive('getMessages')
            ->with('panel')
            ->andReturn(['title', 'body']);

        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);

        $expected = [
            'title' => 'title',
            'theme' => Panel::TYPE_SUCCESS,
            'body' => 'body',
        ];

        // Assert
        $this->assertSame($expected, $result->getVariable('panel'));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithBackRouteToLicenceOverview()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);
        $expectedUrl = 'licence/overview/link';

        // Define Expectations
        $urlHelper = $this->resolveMockService($this->serviceManager, Url::class);
        $urlHelper->shouldReceive('fromRoute')
            ->with(SwitchBoardController::ROUTE_LICENCE_OVERVIEW, [], [], true)
            ->andReturn($expectedUrl);

        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);

        // Assert
        $this->assertSame($expectedUrl, $result->getVariable('backLink'));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithSwitchBoardForm()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);

        // Assert
        $this->assertInstanceOf(Form::class, $result->getVariable('form'));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel_WithSwitchBoardForm
     */
    public function indexAction_SwitchBoardOnlyHasAdd_WhenLicenceHasNoVehicles()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['activeVehicleCount'] = 0;
        $licenceData['totalVehicleCount'] = 0;

        $queryHandler = $this->resolveMockService($this->serviceManager, HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Licence::class))
            ->andReturn($this->setUpQueryResponse($licenceData));

        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);

        // Assert
        $form = $result->getVariable('form');
        $options = $form->get(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)->get(SwitchBoard::FIELD_OPTIONS_NAME)->getValueOptions();

        $this->assertArrayHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD, $options);
        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE, $options);
        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER, $options);
        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REPRINT, $options);
        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW, $options);
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_SwitchBoardRemovesTransferOption_WhenLicenceIsNotMLH()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['isMlh'] = false;

        $queryHandler = $this->resolveMockService($this->serviceManager, HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Licence::class))
            ->andReturn($this->setUpQueryResponse($licenceData));

        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);

        // Assert
        $form = $result->getVariable('form');
        $options = $form->get(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)->get(SwitchBoard::FIELD_OPTIONS_NAME)->getValueOptions();

        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER, $options);
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_SwitchBoardRemovesViewOptionButKeepsViewRemoved_WhenAllVehiclesHaveBeenRemoved()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['activeVehicleCount'] = 0;
        $licenceData['totalVehicleCount'] = 1;

        $queryHandler = $this->resolveMockService($this->serviceManager, HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Licence::class))
            ->andReturn($this->setUpQueryResponse($licenceData));

        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);

        // Assert
        $form = $result->getVariable('form');
        $options = $form->get(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)->get(SwitchBoard::FIELD_OPTIONS_NAME)->getValueOptions();
        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW, $options);
        $this->assertArrayHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED, $options);
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_SwitchBoardHasViewOptionRemovesViewRemoved_WhenNoVehiclesHaveBeenRemoved()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['activeVehicleCount'] = 1;
        $licenceData['totalVehicleCount'] = 1;

        $queryHandler = $this->resolveMockService($this->serviceManager, HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Licence::class))
            ->andReturn($this->setUpQueryResponse($licenceData));

        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);

        // Assert
        $form = $result->getVariable('form');
        $options = $form->get(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)->get(SwitchBoard::FIELD_OPTIONS_NAME)->getValueOptions();
        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED, $options);
        $this->assertArrayHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW, $options);
    }

    /**
     * @test
     * @depends indexAction_IsCallable
     */
    public function indexAction_WithPost_ShouldReturnRedirectToIndexAction_WhenFormIsInvalid()
    {
        // Setup
        $this->setUpSut();
        $this->formValidator()->allows('isValid')->andReturnUsing(function($form) {
            $form->isValid();
            return false;
        });
        $expectedResponse = new Response();
        $request = $this->setUpDecisionRequest(static::A_DECISION_VALUE);

        $routeMatch = new RouteMatch([]);

        // Expect
        $this->redirectHelper()->expects('toRoute')->with(...static::VEHICLES_ROUTE)->andReturn($expectedResponse);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertSame($expectedResponse, $result);
    }

    /**
     * @test
     * @depends      indexAction_IsCallable
     * @depends      indexAction_ReturnsViewModel
     * @dataProvider indexAction_WithPost_ShouldRedirectToPage_DependantOnDecision_Provider
     */
    public function indexAction_WithPost_ShouldRedirectToPage_DependantOnDecision(string $request, int $activeVehicleCount, array $route)
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $redirectHelper = $this->resolveMockService($this->serviceManager, Redirect::class);
        $redirectHelper->expects('toRoute')
            ->withArgs($route)
            ->andReturn($expectedResponse = new Response());

        // Define expectations
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['activeVehicleCount'] = $activeVehicleCount;
        $licenceData['totalVehicleCount'] = 1;

        $queryHandler = $this->resolveMockService($this->serviceManager, HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Licence::class))
            ->andReturn($this->setUpQueryResponse($licenceData));

        // Execute
        $response = $this->sut->indexAction($this->setUpDecisionRequest($request), $routeMatch);

        // Assert
        $this->assertSame($expectedResponse, $response);
    }

    public function indexAction_WithPost_ShouldRedirectToPage_DependantOnDecision_Provider()
    {
        return [
            'Add decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD,
                1,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_ADD,
                    [],
                    [],
                    true,
                ],
            ],
            'Remove Decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE,
                1,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_REMOVE,
                    [],
                    [],
                    true,
                ],
            ],
            'Reprint decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REPRINT,
                1,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_REPRINT,
                    [],
                    [],
                    true,
                ],
            ],
            'Transfer decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER,
                1,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_TRANSFER,
                    [],
                    [],
                    true,
                ],
            ],
            'View decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW,
                1,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_LIST,
                    [],
                    [],
                    true,
                ],
            ],
            'View removed decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED,
                0,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_LIST,
                    [],
                    [
                        'query' => [
                            ListVehicleController::QUERY_KEY_INCLUDE_REMOVED => ''
                        ],
                        'fragment' => ListVehicleController::REMOVE_TABLE_WRAPPER_ID
                    ],
                    true,
                ],
            ]
        ];
    }

    protected function setUp(): void
    {
        $this->setUpServiceManager();
    }

    /**
     * @inheritDoc
     */
    protected function setUpDefaultServices(ServiceManager $serviceManager)
    {
        $this->serviceManager->setService(FlashMessenger::class, $this->setUpMockService(FlashMessenger::class));
        $this->serviceManager->setService(FormHelperService::class, $this->formHelper());
        $this->serviceManager->setService(HandleQuery::class, $this->setupQueryHandler());
        $this->serviceManager->setService(ResponseHelperService::class, $this->setUpMockService(ResponseHelperService::class));
        $this->serviceManager->setService(Url::class, $this->setUpMockService(Url::class));
        $this->serviceManager->setService(LicenceVehicleManagement::class, new LicenceVehicleManagement());
        $this->redirectHelper();
        $this->formValidator();
    }

    /**
     * @return SwitchBoardController
     */
    protected function setUpSut(): SwitchBoardController
    {
        $this->sut = (new SwitchBoardControllerFactory())->__invoke($this->serviceManager, SwitchBoardController::class)->getDelegate();
        return $this->sut;
    }

    /**
     * @return MockInterface|Redirect
     */
    protected function redirectHelper(): MockInterface
    {
        if (! $this->serviceManager->has(Redirect::class)) {
            $instance = $this->setUpMockService(Redirect::class);
            $this->serviceManager->setService(Redirect::class, $instance);
        }
        return $this->serviceManager->get(Redirect::class);
    }

    /**
     * @return MockInterface|FormValidator
     */
    protected function formValidator(): MockInterface
    {
        if (! $this->serviceManager->has(FormValidator::class)) {
            $instance = $this->setUpMockService(FormValidator::class);
            $instance->allows('isValid')->andReturnUsing(function ($form) {

                $form->isValid();
                return true;
            })->byDefault();
            $this->serviceManager->setService(FormValidator::class, $instance);
        }
        return $this->serviceManager->get(FormValidator::class);
    }

    /**
     * @return MockInterface|FormHelperService
     */
    protected function formHelper(): MockInterface
    {
        if (! $this->serviceManager->has(FormHelperService::class)) {
            $instance = $this->setUpMockService(FormHelperService::class);
            $instance->shouldReceive('createForm')->andReturnUsing(function () {
                $annotationBuilder = new AnnotationBuilder();
                $form = $annotationBuilder->createForm(SwitchBoard::class);
                return $form;
            })->byDefault();
            $this->serviceManager->setService(FormHelperService::class, $instance);
        }
        return $this->serviceManager->get(FormHelperService::class);
    }

    /**
     * @return array
     */
    protected function setUpDefaultLicenceData(): array
    {
        return [
            'id' => 1,
            'licNo' => 'OB1234567',
            'isMlh' => true,
            'activeVehicleCount' => 1,
            'totalVehicleCount' => 2,
        ];
    }

    /**
     * @return HandleQuery|m\LegacyMockInterface|MockInterface
     */
    protected function setupQueryHandler()
    {
        $instance = m::mock(HandleQuery::class);
        $instance->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Licence::class))
            ->andReturnUsing(function () {
                return $this->setUpQueryResponse(
                    $this->setUpDefaultLicenceData()
                );
            })
            ->byDefault();
        return $instance;
    }

    /**
     * @param mixed $data
     * @return QueryResponse|MockInterface
     */
    protected function setUpQueryResponse(array $data): QueryResponse
    {
        $response = new QueryResponse(new Response());
        $response->setResult($data);
        return $response;
    }

    /**
     * @return Request
     */
    protected function setUpDecisionRequest(string $value): Request
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(
            new Parameters([
                    SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME => [
                        SwitchBoard::FIELD_OPTIONS_NAME => $value
                    ]
            ])
        );
        return $request;
    }
}
