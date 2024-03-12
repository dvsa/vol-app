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
use Common\View\Helper\Panel;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Hamcrest\Core\IsInstanceOf;
use Interop\Container\Containerinterface;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Router\Http\RouteMatch;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Olcs\Controller\Licence\Vehicle\ListVehicleController;
use Olcs\Controller\Licence\Vehicle\SwitchBoardController;
use Olcs\Form\Model\Form\Vehicle\SwitchBoard;
use Olcs\Session\LicenceVehicleManagement;

class SwitchBoardControllerTest extends MockeryTestCase
{
    /**
     * @var FlashMessenger
     */
    private $flashMessengerMock;

    /**
     * @var FormHelperService
     */
    private $formHelperMock;

    /**
     * @var HandleQuery
     */
    private $queryHandlerMock;

    /**
     * @var Redirect
     */
    private $redirectHelperMock;

    /**
     * @var ResponseHelperService
     */
    private $responseHelperMock;

    /**
     * @var LicenceVehicleManagement
     */
    private $sessionMock;

    /**
     * @var Url
     */
    private $urlHelperMock;

    /**
     * @var FormValidator
     */
    private $formValidatorMock;

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
     */
    public function indexAction_ReturnsViewModel()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);
        // Define Expectations
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')->withAnyArgs();
        $this->urlHelperMock->shouldReceive('fromRoute')->withAnyArgs();
        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel_WithPanel_WhenFlashMessageHasPanelNamespace()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->urlHelperMock->shouldReceive('fromRoute')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')
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
     */
    public function indexAction_ReturnsViewModel_WithPanelBody_WhenFlashMessageHasPanelNamespaceSecondMessage()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->urlHelperMock->shouldReceive('fromRoute')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')
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
     */
    public function indexAction_ReturnsViewModel_WithBackRouteToLicenceOverview()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);
        $expectedUrl = 'licence/overview/link';

        // Define Expectations
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')->withAnyArgs();
        $this->urlHelperMock->shouldReceive('fromRoute')
            ->with(SwitchBoardController::ROUTE_LICENCE_OVERVIEW, [], [], true)
            ->andReturn($expectedUrl);

        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);

        // Assert
        $this->assertSame($expectedUrl, $result->getVariable('backLink'));
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel_WithSwitchBoardForm()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')->withAnyArgs();
        $this->urlHelperMock->shouldReceive('fromRoute')->withAnyArgs();
        // Execute
        $result = $this->sut->indexAction(new Request(), $routeMatch);
        // Assert
        $this->assertInstanceOf(Form::class, $result->getVariable('form'));
    }

    /**
     * @test
     */
    public function indexAction_SwitchBoardOnlyHasAdd_WhenLicenceHasNoVehicles()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')->withAnyArgs();
        $this->urlHelperMock->shouldReceive('fromRoute')->withAnyArgs();
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['activeVehicleCount'] = 0;
        $licenceData['totalVehicleCount'] = 0;

        $this->queryHandlerMock->shouldReceive('__invoke')
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
     */
    public function indexAction_SwitchBoardRemovesTransferOption_WhenLicenceIsNotMLH()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')->withAnyArgs();
        $this->urlHelperMock->shouldReceive('fromRoute')->withAnyArgs();
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['isMlh'] = false;

        $this->queryHandlerMock->shouldReceive('__invoke')
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
     */
    public function indexAction_SwitchBoardRemovesViewOptionButKeepsViewRemoved_WhenAllVehiclesHaveBeenRemoved()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')->withAnyArgs();
        $this->urlHelperMock->shouldReceive('fromRoute')->withAnyArgs();
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['activeVehicleCount'] = 0;
        $licenceData['totalVehicleCount'] = 1;

        $this->queryHandlerMock->shouldReceive('__invoke')
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
     */
    public function indexAction_SwitchBoardHasViewOptionRemovesViewRemoved_WhenNoVehiclesHaveBeenRemoved()
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')->withAnyArgs();
        $this->urlHelperMock->shouldReceive('fromRoute')->withAnyArgs();
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['activeVehicleCount'] = 1;
        $licenceData['totalVehicleCount'] = 1;

        $this->queryHandlerMock->shouldReceive('__invoke')
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
     */
    public function indexAction_WithPost_ShouldReturnRedirectToIndexAction_WhenFormIsInvalid()
    {
        // Setup
        $this->setUpSut();
        $this->formValidatorMock->allows('isValid')->andReturnUsing(function ($form) {
            $form->isValid();
            return false;
        });
        $expectedResponse = new Response();
        $request = $this->setUpDecisionRequest(static::A_DECISION_VALUE);

        $routeMatch = new RouteMatch([]);
        // Define expectations
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('addMessage')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('addMessages')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')->withAnyArgs();

        // Expect
        $this->redirectHelperMock->expects('toRoute')->with(...static::VEHICLES_ROUTE)->andReturn($expectedResponse);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertSame($expectedResponse, $result);
    }

    /**
     * @test
     * @dataProvider indexAction_WithPost_ShouldRedirectToPage_DependantOnDecision_Provider
     */
    public function indexAction_WithPost_ShouldRedirectToPage_DependantOnDecision(string $request, int $activeVehicleCount, array $route)
    {
        // Setup
        $this->setUpSut();
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $this->redirectHelperMock->expects('toRoute')
            ->withArgs($route)
            ->andReturn($expectedResponse = new Response());

        // Define expectations
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['activeVehicleCount'] = $activeVehicleCount;
        $licenceData['totalVehicleCount'] = 1;

        $this->queryHandlerMock->shouldReceive('__invoke')
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
        $this->flashMessengerMock = m::mock(FlashMessenger::class);
        $this->formHelperMock  = m::mock(FormHelperService::class);
        $this->queryHandlerMock  = m::mock(HandleQuery::class);
        $this->redirectHelperMock  = m::mock(Redirect::class);
        $this->responseHelperMock  = m::mock(ResponseHelperService::class);
        $this->sessionMock  = new LicenceVehicleManagement();
        $this->urlHelperMock  = m::mock(Url::class);
        $this->formValidatorMock  = m::mock(FormValidator::class);
    }

    protected function setUpSut()
    {
        // Create a mock container (similar to a service manager)
        $this->formHelper();
        $this->setupQueryHandler();
        $this->formValidator();

        $this->sut = new SwitchBoardController(
            $this->flashMessengerMock,
            $this->formHelperMock,
            $this->queryHandlerMock,
            $this->redirectHelperMock,
            $this->responseHelperMock,
            $this->sessionMock,
            $this->urlHelperMock,
            $this->formValidatorMock
        );
    }

    protected function formValidator()
    {
        $this->formValidatorMock->allows('isValid')->andReturnUsing(function ($form) {
            $form->isValid();
            return true;
        })->byDefault();
    }

    protected function formHelper()
    {
        $this->formHelperMock->shouldReceive('createForm')->andReturnUsing(function () {
            $annotationBuilder = new AnnotationBuilder();
            return $annotationBuilder->createForm(SwitchBoard::class);
        })->byDefault();
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

    protected function setupQueryHandler()
    {
        $this->queryHandlerMock->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Licence::class))
            ->andReturnUsing(fn() => $this->setUpQueryResponse(
                $this->setUpDefaultLicenceData()
            ))
            ->byDefault();
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
