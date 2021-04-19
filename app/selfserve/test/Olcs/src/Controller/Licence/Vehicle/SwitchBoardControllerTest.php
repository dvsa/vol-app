<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Form\Form;
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
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\MockInterface;
use Olcs\Controller\Licence\Vehicle\SwitchBoardController;
use Olcs\Controller\Licence\Vehicle\SwitchBoardControllerFactory;
use Olcs\Form\Model\Form\Vehicle\SwitchBoard;
use Olcs\Session\LicenceVehicleManagement;

class SwitchBoardControllerTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @test
     */
    public function indexAction_IsCallable()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();

        $sut = $this->setUpSut($serviceLocator);

        // Assert
        $this->assertIsCallable([$sut, 'indexAction']);
    }

    /**
     * @test
     * @depends indexAction_IsCallable
     */
    public function indexAction_ReturnsViewModel()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $sut->indexAction(new Request(), $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsVierwModel_WithPanel_WhenFlashMessageHasPanelNamespace()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $flashMessenger = $this->resolveMockService($serviceLocator, FlashMessenger::class);
        $flashMessenger->shouldReceive('getMessages')
            ->with('panel')
            ->andReturn(['title']);

        // Execute
        $result = $sut->indexAction(new Request(), $routeMatch);

        $expected = [
            'title' => 'title',
            'theme' => Panel::TYPE_SUCCESS
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
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);
        $routeMatch = new RouteMatch([]);
        $expectedUrl = 'licence/overview/link';

        // Define Expectations
        $urlHelper = $this->resolveMockService($serviceLocator, Url::class);
        $urlHelper->shouldReceive('fromRoute')
            ->with(SwitchBoardController::ROUTE_LICENCE_OVERVIEW, [], [], true)
            ->andReturn($expectedUrl);

        // Execute
        $result = $sut->indexAction(new Request(), $routeMatch);

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
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $sut->indexAction(new Request(), $routeMatch);

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
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['activeVehicleCount'] = 0;
        $licenceData['totalVehicleCount'] = 0;

        $queryHandler = $this->resolveMockService($serviceLocator, HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Licence::class))
            ->andReturn($this->setUpQueryResponse($licenceData));

        // Execute
        $result = $sut->indexAction(new Request(), $routeMatch);

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
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['isMlh'] = false;

        $queryHandler = $this->resolveMockService($serviceLocator, HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Licence::class))
            ->andReturn($this->setUpQueryResponse($licenceData));

        // Execute
        $result = $sut->indexAction(new Request(), $routeMatch);

        // Assert
        $form = $result->getVariable('form');
        $options = $form->get(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)->get(SwitchBoard::FIELD_OPTIONS_NAME)->getValueOptions();

        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER, $options);

    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_SwitchBoardRewordsViewOption_WhenAllVehiclesHaveBeenRemoved()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['activeVehicleCount'] = 0;
        $licenceData['totalVehicleCount'] = 1;

        $queryHandler = $this->resolveMockService($serviceLocator, HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Licence::class))
            ->andReturn($this->setUpQueryResponse($licenceData));

        // Execute
        $result = $sut->indexAction(new Request(), $routeMatch);

        // Assert
        $form = $result->getVariable('form');
        $options = $form->get(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)->get(SwitchBoard::FIELD_OPTIONS_NAME)->getValueOptions();
        $label = $options[SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW]['label'];

        $this->assertSame('licence.vehicle.switchboard.form.view.label-removed', $label);
    }

    /**
     * @test
     */
    public function decisionAction_IsCallable()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();

        $sut = $this->setUpSut($serviceLocator);

        // Assert
        $this->assertIsCallable([$sut, 'decisionAction']);
    }

    /**
     * @test
     * @depends decisionAction_IsCallable
     */
    public function decisionAction_ShouldReturnIndexAction_WhenFormIsInavlid()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);
        $routeMatch = new RouteMatch([]);
        $request = $this->setUpDecisionRequest('foo');

        // Execute
        $result = $sut->decisionAction($request, $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);

    }

    /**
     * @test
     * @depends      decisionAction_IsCallable
     * @depends      indexAction_ReturnsViewModel
     * @dataProvider decisionAction_ShouldRedirectToPage_DependantOnDecision_Provider
     */
    public function decisionAction_ShouldRedirectToPage_DependantOnDecision(string $request, string $route)
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);
        $routeMatch = new RouteMatch([]);

        // Define expectations
        $redirectHelper = $this->resolveMockService($serviceLocator, Redirect::class);
        $redirectHelper->shouldReceive('toRoute')
            ->with($route, [], [], true)
            ->andReturn($expectedResponse = new Response());

        // Execute
        $response = $sut->decisionAction($this->setUpDecisionRequest($request), $routeMatch);

        // Assert
        $this->assertSame($expectedResponse, $response);
    }

    public function decisionAction_ShouldRedirectToPage_DependantOnDecision_Provider()
    {
        return [
            'Add decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD,
                SwitchBoardController::ROUTE_LICENCE_VEHICLE_ADD

            ],
            'Remove Decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE,
                SwitchBoardController::ROUTE_LICENCE_VEHICLE_REMOVE
            ],
            'Reprint decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD,
                SwitchBoardController::ROUTE_LICENCE_VEHICLE_ADD
            ],
            'Transfer decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD,
                SwitchBoardController::ROUTE_LICENCE_VEHICLE_ADD
            ],
            'View decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD,
                SwitchBoardController::ROUTE_LICENCE_VEHICLE_ADD
            ]
        ];
    }

    /**
     * Sets up default services.
     *
     * @return array
     */
    public function setUpDefaultServices()
    {
        return [
            FlashMessenger::class => $this->setUpMockService(FlashMessenger::class),
            FormHelperService::class => $this->setUpFormHelper(),
            HandleQuery::class => $this->setupQueryHandler(),
            Redirect::class => $this->setUpMockService(Redirect::class),
            ResponseHelperService::class => $this->setUpMockService(ResponseHelperService::class),
            Url::class => $this->setUpMockService(Url::class),
            LicenceVehicleManagement::class => new LicenceVehicleManagement()
        ];
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return SwitchBoardController
     */
    protected function setUpSut(ServiceLocatorInterface $serviceLocator): SwitchBoardController
    {
        return (new SwitchBoardControllerFactory())->__invoke($serviceLocator, SwitchBoardController::class)->getDelegate();
    }

    /**
     * @return FormHelperService
     */
    protected function setUpFormHelper(): FormHelperService
    {
        $instance = $this->setUpMockService(FormHelperService::class);
        $instance->shouldReceive('createForm')->andReturnUsing(function () {
            $annotationBuilder = new AnnotationBuilder();
            $form = $annotationBuilder->createForm(SwitchBoard::class);
            return $form;
        })->byDefault();
        return $instance;
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
        $response = new QueryResponse(new HttpResponse());
        $response->setResult($data);
        return $response;
    }

    /**
     * @return Request
     */
    protected function setUpDecisionRequest(string $value): Request
    {
        $request = new Request();
        $request->setPost(
            new Parameters([
                    SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME => [
                        SwitchBoard::FIELD_OPTIONS_NAME => $value
                    ]
                ]
            )
        );
        return $request;
    }
}
