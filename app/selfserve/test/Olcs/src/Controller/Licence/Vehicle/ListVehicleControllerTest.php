<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Common\Test\Builder\ServiceManagerBuilder;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateVehicles;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Hamcrest\Arrays\IsArrayContainingKeyValuePair;
use Hamcrest\Core\IsAnything;
use Hamcrest\Core\IsIdentical;
use Hamcrest\Arrays\IsArrayContainingKey;
use Hamcrest\Core\IsInstanceOf;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Mockery\MockInterface;
use Olcs\Controller\Licence\Vehicle\ListVehicleController;

class ListVehicleControllerTest extends MockeryTestCase
{
    /**
     * @test
     */
    public function indexAction_IsCallable()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);

        // Assert
        $this->assertIsCallable([$sut, 'indexAction']);
    }

    /**
     * @test
     */
    public function postAction_IsCallable()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);

        // Assert
        $this->assertIsCallable([$sut, 'postAction']);
    }

    /**
     * @depends indexAction_IsCallable
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WhenNoFormatIsProvided()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @depends indexAction_IsCallable
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $request->setQuery(new Parameters(['format' => ListVehicleController::FORMAT_HTML]));
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WithLicence()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);

        $queryHandler = $serviceManager->get(HandleQuery::class);
        assert($queryHandler instanceof MockInterface, 'Expected instance of MockInterface');
        $licence = ['id' => 1, 'licNo' => 'foo'];
        $licenceQueryMatcher = IsInstanceOf::anInstanceOf(Licence::class);
        $licenceQueryResponse = m::mock(Response::class);
        $licenceQueryResponse->shouldIgnoreMissing();
        $licenceQueryResponse->shouldReceive('getResult')->andReturn($licence);
        $queryHandler->shouldReceive('__invoke')->with($licenceQueryMatcher)->andReturn($licenceQueryResponse);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertSame($licence, $result->getVariables()['licence'] ?? null);
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WithExportCurrentAndRemovedCsvAction()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertArrayHasKey('exportCurrentAndRemovedCsvAction', $result->getVariables());
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WithExportCurrentAndRemovedCsvAction_WithFormatQueryParameter()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $licenceId = 1;
        $routeMatch = new RouteMatch($routeParams = ['licence' => $licenceId]);
        $urlHelper = $serviceManager->get(Url::class);
        assert($urlHelper instanceof MockInterface, 'Expected instance of MockInterface');
        $expectedUrl = "abcdefg";

        // Define Expectations
        $queryMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('format', 'csv');
        $optionsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('query', $queryMatcher);
        $urlHelper->shouldReceive('fromRoute')->with('licence/vehicle/list/GET', $routeParams, $optionsMatcher)->andReturn($expectedUrl);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertEquals($expectedUrl, $result->getVariables()['exportCurrentAndRemovedCsvAction']);
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WithExportCurrentAndRemovedCsvAction_WithIncludeRemovedQueryParameter()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $licenceId = 1;
        $routeMatch = new RouteMatch($routeParams = ['licence' => $licenceId]);
        $urlHelper = $serviceManager->get(Url::class);
        assert($urlHelper instanceof MockInterface, 'Expected instance of MockInterface');
        $expectedUrl = "abcdefg";

        // Define Expectations
        $queryMatcher = IsArrayContainingKey::hasKeyInArray('includeRemoved');
        $optionsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('query', $queryMatcher);
        $urlHelper->shouldReceive('fromRoute')->with('licence/vehicle/list/GET', $routeParams, $optionsMatcher)->andReturn($expectedUrl);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertEquals($expectedUrl, $result->getVariables()['exportCurrentAndRemovedCsvAction']);
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_AndConfiguresCurrentVehicleTable_Query()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $request->setQuery(new Parameters($query = [
            ListVehicleController::QUERY_KEY_SORT_CURRENT_VEHICLES_TABLE => 'foo',
            ListVehicleController::QUERY_KEY_ORDER_CURRENT_VEHICLES_TABLE => 'bar',
            'limit' => 56,
        ]));
        $routeMatch = new RouteMatch([]);
        $tableBuilder = $this->setUpTableBuilder();
        $tableFactory = $serviceManager->get(TableFactory::class);
        assert($tableFactory instanceof MockInterface, 'Expected instance of MockInterface');
        $tableFactory->shouldReceive('getTableBuilder')->andReturn($tableBuilder);

        // Define Expectations
        $queryMatcher = IsIdentical::identicalTo($query);
        $paramsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('query', $queryMatcher);
        $any = IsAnything::anything();
        $tableBuilder->shouldReceive('prepareTable')->with($any, $any, $paramsMatcher)->once()->andReturnSelf();

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_AndConfiguresCurrentVehicleTable_Page_WhenNoPageIsSetOnARequest()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);
        $tableBuilder = $this->setUpTableBuilder();
        $tableFactory = $serviceManager->get(TableFactory::class);
        assert($tableFactory instanceof MockInterface, 'Expected instance of MockInterface');
        $tableFactory->shouldReceive('getTableBuilder')->andReturn($tableBuilder);

        // Define Expectations
        $paramsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('page', 1);
        $any = IsAnything::anything();
        $tableBuilder->shouldReceive('prepareTable')->with($any, $any, $paramsMatcher)->once()->andReturnSelf();

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_AndConfiguresCurrentVehicleTable_Page_WhenEmptyPageIsSetOnARequest()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $request->setQuery(new Parameters(['page' => '']));
        $routeMatch = new RouteMatch([]);
        $tableBuilder = $this->setUpTableBuilder();
        $tableFactory = $serviceManager->get(TableFactory::class);
        assert($tableFactory instanceof MockInterface, 'Expected instance of MockInterface');
        $tableFactory->shouldReceive('getTableBuilder')->andReturn($tableBuilder);

        // Define Expectations
        $paramsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('page', 1);
        $any = IsAnything::anything();
        $tableBuilder->shouldReceive('prepareTable')->with($any, $any, $paramsMatcher)->once()->andReturnSelf();

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    /**
     * @depends postAction_IsCallable
     * @test
     */
    public function postAction_RespondsInHtmlFormat_SetsUserOCRSOptInPreference_CheckboxValidValuesRunsCommand()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);
        $commandHandler = $this->resolveMockService($serviceManager, HandleCommand::class);
        $formHelper = $this->resolveMockService($serviceManager, FormHelperService::class);
        $mockForm = $this->setUpForm();
        $mockForm->shouldReceive('getData')->andReturn(['ocrsCheckbox' => $expected = 'Y']);
        $formHelper->shouldReceive('createForm')->andReturn($mockForm);

        // Define Expectations
        $commandHandler
            ->shouldReceive('__invoke')
            ->withArgs(function($command) use ($expected) {
                return $command instanceof UpdateVehicles && $command->getShareInfo() === $expected;
            })
            ->once()
            ->andReturn(null);

        // Execute
        $sut->postAction($request, $routeMatch);
    }

    /**
     * @depends postAction_IsCallable
     * @test
     */
    public function postAction_RespondsInHtmlFormat_SetsUserOCRSOptInPreference_CheckboxInvalidValues_ReturnsIndexActionWithErrors()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);
        $commandHandler = $this->resolveMockService($serviceManager, HandleCommand::class);
        $formHelper = $this->resolveMockService($serviceManager, FormHelperService::class);
        $mockForm = $this->setUpForm();
        $mockForm->shouldReceive('isValid')->andReturnFalse();
        $formHelper->shouldReceive('createForm')->andReturn($mockForm);

        // Define Expectations
        $updateVehicleCommandMatcher = IsInstanceOf::anInstanceOf(UpdateVehicles::class);
        $commandHandler->shouldReceive('__invoke')->with($updateVehicleCommandMatcher)->never();

        // Execute
        $sut->postAction($request, $routeMatch);
    }


//    @todo Re-add in VOL-136
//
//    public function setUpRemovedTableTitleData()
//    {
//        return [
//            'title when table total not equal to one' => [2, 'licence.vehicle.list.section.removed.header.title.plural'],
//            'title when table total is one' => [1, 'licence.vehicle.list.section.removed.header.title.singular'],
//        ];
//    }
//
//    /**
//     * @depends indexActionRespondsInHtmlFormatWhenHtmlFormatIsProvided
//     * @dataProvider setUpRemovedTableTitleData
//     * @test
//     */
//    public function indexActionRespondsInHtmlFormatWithPluralRemovedVehicleTableTitle(int $total, string $expectedTranslationKey)
//    {
//        // Setup
//        $serviceManager = $this->newServiceManager();
//        $sut = $this->newSut($serviceManager);
//        $request = new Request();
//        $request->setQuery(new Parameters(['includeRemoved' => '']));
//        $routeMatch = new RouteMatch([]);
//        $expectedTitle = 'foo';
//
//        $tableBuilder = $this->setUpTableBuilder();
//        $tableBuilder->shouldReceive('getTotal')->andReturn($total);
//        $tableBuilder->shouldReceive('getLimit')->andReturn($total);
//
//        $tableBuilderFactory = $serviceManager->get(TableFactory::class);
//        assert($tableBuilderFactory instanceof MockInterface, 'Expected instance of MockInterface');
//        $tableBuilderFactory->shouldReceive('getTableBuilder')->andReturn($tableBuilder);
//
//        $translator = $serviceManager->get(TranslationHelperService::class);
//        assert($translator instanceof MockInterface, 'Expected instance of MockInterface');
//
//        // Define Expectations
//        $translator->shouldReceive('translateReplace')->once()->with($expectedTranslationKey, [$total])->andReturn($expectedTitle);
//
//        // Execute
//        $result = $sut->indexAction($request, $routeMatch);
//        $title = $result->getVariable('removedVehicleTableTitle');
//
//        // Assert
//        $this->assertEquals($expectedTitle, $title);
//    }

    /**
     * Sets up default services.
     *
     * @todo implement an interface around this method
     *
     * @return array
     */
    public function setUpDefaultServices()
    {
        return [
            TranslationHelperService::class => $this->setUpTranslator(),
            HandleCommand::class => $this->setUpCommandHandler(),
            HandleQuery::class => $this->setUpQueryHandler(),
            Url::class => $this->setUpUrlHelper(),
            ResponseHelperService::class => $this->setUpResponseHelper(),
            TableFactory::class => $this->setUpTableFactory(),
            FormHelperService::class => $this->setUpFormHelper(),
            FlashMessengerHelperService::class => $this->setUpFlashMessengerHelperService(),
        ];
    }

    /**
     * @param ServiceManager $serviceManager
     * @return ListVehicleController
     */
    protected function setUpSut(ServiceManager $serviceManager)
    {
        $translationService = $serviceManager->get(TranslationHelperService::class);
        $commandHandler = $serviceManager->get(HandleCommand::class);
        $queryHandler = $serviceManager->get(HandleQuery::class);
        $urlHelper = $serviceManager->get(Url::class);
        $responseHelper = $serviceManager->get(ResponseHelperService::class);
        $tableFactory = $serviceManager->get(TableFactory::class);
        $formHelper = $serviceManager->get(FormHelperService::class);
        $flashMessengerHelper = $serviceManager->get(FlashMessengerHelperService::class);
        return new ListVehicleController($commandHandler, $queryHandler, $translationService, $urlHelper, $responseHelper, $tableFactory, $formHelper, $flashMessengerHelper);
    }

    /**
     * @return ServiceManager
     */
    protected function setUpServiceManager(): ServiceManager
    {
        return (new ServiceManagerBuilder($this))->build();
    }

    /**
     * @return HandleQuery
     */
    protected function setUpQueryHandler(): HandleQuery
    {
        $instance = m::mock(HandleQuery::class);
        $instance->shouldIgnoreMissing();

        $response = m::mock(Response::class);
        $response->shouldIgnoreMissing();
        $response->shouldReceive('getResult')->andReturn(['count' => 0, 'results' => []])->byDefault();
        $instance->shouldReceive('__invoke')->andReturn($response)->byDefault();

        return $instance;
    }

    /**
     * @return HandleCommand
     */
    protected function setUpCommandHandler(): HandleCommand
    {
        $instance = m::mock(HandleCommand::class);
        $instance->shouldIgnoreMissing();

        $response = m::mock(Response::class);
        $response->shouldIgnoreMissing();
        $instance->shouldReceive('__invoke')->andReturn($response)->byDefault();

        return $instance;
    }

    /**
     * @return TableFactory
     */
    protected function setUpTableFactory(): TableFactory
    {
        $instance = m::mock(TableFactory::class);
        $instance->shouldIgnoreMissing();
        $instance->shouldReceive('getTableBuilder')->andReturnUsing([$this, 'setUpTableBuilder'])->byDefault();
        return $instance;
    }

    /**
     * @return MockInterface
     */
    public function setUpTableBuilder(): MockInterface
    {
        $tableBuilder = m::mock(TableBuilder::class);
        $tableBuilder->shouldIgnoreMissing($tableBuilder);
        $tableBuilder->shouldReceive('getSettings')->andReturn([])->byDefault();
        return $tableBuilder;
    }

    /**
     * @return TranslationHelperService
     */
    protected function setUpTranslator(): TranslationHelperService
    {
        $instance = m::mock(TranslationHelperService::class);
        $instance->shouldIgnoreMissing('');
        return $instance;
    }

    /**
     * @return Url
     */
    protected function setUpUrlHelper(): Url
    {
        $instance = m::mock(Url::class);
        $instance->shouldIgnoreMissing('');
        return $instance;
    }

    /**
     * @return ResponseHelperService
     */
    protected function setUpResponseHelper(): ResponseHelperService
    {
        $instance = m::mock(ResponseHelperService::class);
        $instance->shouldIgnoreMissing();
        return $instance;
    }

    /**
     * @return FormHelperService
     */
    protected function setUpFormHelper(): FormHelperService
    {
        $instance = m::mock(FormHelperService::class);
        $instance->shouldIgnoreMissing();

        $mockForm = $this->setUpForm();
        $instance->shouldReceive('createForm')->andReturn($mockForm)->byDefault();

        return $instance;
    }

    /**
     * @return FlashMessengerHelperService
     */
    protected function setUpFlashMessengerHelperService(): FlashMessengerHelperService
    {
        $instance = m::mock(FlashMessengerHelperService::class);
        $instance->shouldIgnoreMissing();
        return $instance;
    }

    /**
     * @return MockInterface
     */
    protected function setUpForm(): MockInterface
    {
        $mockForm = m::mock(\Laminas\Form\Form::class);
        $mockForm->shouldIgnoreMissing();
        $mockForm->shouldReceive('isValid')->andReturnTrue()->byDefault();

        return $mockForm;
    }

    /**
     * Resolves a mock service from a service container.
     *
     * @param ServiceLocatorInterface $serviceManager
     * @param string $service
     * @return MockInterface
     */
    protected function resolveMockService(ServiceLocatorInterface $serviceManager, string $service): MockInterface
    {
        $service = $serviceManager->get($service);
        assert($service instanceof MockInterface, 'Expected instance of MockInterface');
        return $service;
    }
}
