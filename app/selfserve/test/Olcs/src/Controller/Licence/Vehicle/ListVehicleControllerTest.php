<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Form\Elements\Types\AbstractInputSearch;
use Common\Service\Cqrs\Response as QueryResponse;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Common\Test\Builder\ServiceManagerBuilder;
use Common\Test\MockeryTestCase;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateVehicles;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\Licence\Vehicles;
use Hamcrest\Arrays\IsArrayContainingKeyValuePair;
use Hamcrest\Core\IsAnything;
use Hamcrest\Core\IsIdentical;
use Hamcrest\Arrays\IsArrayContainingKey;
use Hamcrest\Core\IsInstanceOf;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\Parameters;
use Laminas\Uri\Http;
use Laminas\Validator\Translator\TranslatorInterface;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\MockInterface;
use Olcs\Controller\Licence\Vehicle\ListVehicleController;
use Olcs\Controller\Licence\Vehicle\ListVehicleControllerFactory;
use Olcs\Form\Model\Form\Vehicle\ListVehicleSearch;
use Olcs\Table\TableEnum;

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
        $request = $this->setUpRequest('/');
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
        $request = $this->setUpRequest('/');
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
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);

        $queryHandler = $serviceManager->get(HandleQuery::class);
        assert($queryHandler instanceof MockInterface, 'Expected instance of MockInterface');
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceQueryMatcher = IsInstanceOf::anInstanceOf(Licence::class);
        $licenceQueryResponse = m::mock(QueryResponse::class);
        $licenceQueryResponse->shouldIgnoreMissing();
        $licenceQueryResponse->shouldReceive('getResult')->andReturn($licenceData);
        $queryHandler->shouldReceive('__invoke')->with($licenceQueryMatcher)->andReturn($licenceQueryResponse);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertSame($licenceData, $result->getVariables()['licence'] ?? null);
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
        $request = $this->setUpRequest('/');
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
        $request = $this->setUpRequest('/');
        $licenceId = 1;
        $routeMatch = new RouteMatch($routeParams = ['licence' => $licenceId]);
        $urlHelper = $this->resolveMockService($serviceManager, Url::class);
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
        $request = $this->setUpRequest('/');
        $licenceId = 1;
        $routeMatch = new RouteMatch($routeParams = ['licence' => $licenceId]);
        $urlHelper = $this->resolveMockService($serviceManager, Url::class);
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
     * @test
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     */
    public function indexAction_RespondsInHtmlFormat_AndConfiguresCurrentVehicleTable_Query()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);

        $query = [
            ListVehicleController::QUERY_KEY_SORT_CURRENT_VEHICLES => 'v.vrm',
            ListVehicleController::QUERY_KEY_ORDER_CURRENT_VEHICLES => 'ASC',
            'limit' => 56,
        ];
        $request = $this->setUpRequest('/');
        $request->setQuery(new Parameters($query));
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $queryMatcher = IsIdentical::identicalTo($query);
        $paramsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('query', $queryMatcher);
        $this->expectTableToBePrepared($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_CURRENT, null, $paramsMatcher);

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
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $paramsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('page', 1);
        $this->expectTableToBePrepared($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_CURRENT, null, $paramsMatcher);

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
        $request = $this->setUpRequest('/');
        $request->setQuery(new Parameters(['page' => '']));
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $paramsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('page', 1);
        $this->expectTableToBePrepared($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_CURRENT, null, $paramsMatcher);

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    public function setUpRemovedTableTitleData()
    {
        return [
            'title when table total not equal to one' => [2, 'licence.vehicle.list.section.removed.header.title.plural'],
            'title when table total is one' => [1, 'licence.vehicle.list.section.removed.header.title.singular'],
        ];
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @dataProvider setUpRemovedTableTitleData
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WithCorrectRemovedVehicleTableTitle(int $total, string $expectedTranslationKey)
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $request->setQuery(new Parameters(['includeRemoved' => '']));
        $routeMatch = new RouteMatch([]);
        $expectedTitle = 'foo';
        $results = array_fill(0, $total, ['id' => 6]);
        $this->injectRemovedVehiclesQueryResultData($serviceManager, ['count' => $total, 'results' => $results]);
        $translator = $this->resolveMockService($serviceManager, TranslationHelperService::class);

        // Define Expectations
        $translator->shouldReceive('translateReplace')->once()->with($expectedTranslationKey, [count($results)])->andReturn($expectedTitle);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);
        $title = $result->getVariable('removedVehicleTableTitle');

        // Assert
        $this->assertEquals($expectedTitle, $title);
    }

    /**
     * @test
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     */
    public function indexAction_SetShowRemovedVehiclesToFalse_WhenALicenceHasNoRemovedVehicles()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($serviceManager, ['count' => 0, 'results' => []]);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $matcher = IsArrayContainingKeyValuePair::hasKeyValuePair('showRemovedVehicles', false);
        $this->assertTrue($matcher->matches((array) $result->getVariables()), 'Expected result variables to have a key "showRemovedVehicles" with a value of false');
    }

    /**
     * @test
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     */
    public function indexAction_SetShowRemovedVehiclesToTrue_WhenALicenceHasOneRemovedVehicle()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($serviceManager, ['count' => 1, 'results' => []]);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $matcher = IsArrayContainingKeyValuePair::hasKeyValuePair('showRemovedVehicles', true);
        $this->assertTrue($matcher->matches((array) $result->getVariables()), 'Expected result variables to have a key "showRemovedVehicles" with a value of true');
    }

    /**
     * @test
     * @depends indexAction_SetShowRemovedVehiclesToTrue_WhenALicenceHasOneRemovedVehicle
     */
    public function indexAction_SetsExpandRemovedVehicles_WhenQueryParamIsSet_AndALicenceHasOneRemovedVehicle()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $request->setQuery(new Parameters(['includeRemoved' => '']));
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($serviceManager, ['count' => 1, 'results' => []]);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertTrue($result->getVariable('showRemovedVehiclesExpanded'));
    }

    /**
     * @test
     * @depends indexAction_SetShowRemovedVehiclesToTrue_WhenALicenceHasOneRemovedVehicle
     */
    public function indexAction_DoesNotSetExpandRemovedVehicles_WhenQueryParamIsSet_AndThereAreNoRemovedVehicles()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $request->setQuery(new Parameters(['includeRemoved' => '']));
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($serviceManager, ['count' => 0, 'results' => []]);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertArrayNotHasKey('showRemovedVehiclesExpanded', $result->getVariables());
    }

    /**
     * @return array
     */
    public function buttonTranslationKeyTypes(): array
    {
        return [
            'title' => ['title'],
            'label' => ['label'],
        ];
    }

    /**
     * @test
     */
    public function indexAction_DoesNotSetToggleRemovedVehiclesAction_WhenNoRemovedVehicles()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('foobarbaz');
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($serviceManager, ['count' => 0, 'results' => []]);

        // Execute
        $variables = (array) ($sut->indexAction($request, $routeMatch)->getVariables());

        // Assert
        $this->assertArrayNotHasKey('toggleRemovedAction', $variables);
    }

    /**
     * @test
     */
    public function indexAction_DoesNotSetToggleRemovedVehiclesAction_WhenSearching()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('foobarbaz', ['vehicleSearch' => []]);
        $routeMatch = new RouteMatch([]);

        // Execute
        $variables = (array) ($sut->indexAction($request, $routeMatch)->getVariables());

        // Assert
        $this->assertArrayNotHasKey('toggleRemovedAction', $variables);
    }

    /**
     * @test
     */
    public function indexAction_SetToggleRemovedVehiclesAction_ToShowRemovedVehicles_WhenNoQueryParamIsSet()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('foobarbaz');
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($serviceManager, ['count' => 1, 'results' => []]);

        // Execute
        $variables = (array) ($sut->indexAction($request, $routeMatch)->getVariables());

        // Assert
        $expectedQueryParam = ListVehicleController::QUERY_KEY_INCLUDE_REMOVED;
        $message = sprintf('Expected route reference to have "%s" query parameter set', $expectedQueryParam);
        $notExpectedString = sprintf("%s=", $expectedQueryParam);
        $this->assertStringNotContainsString($notExpectedString, $variables['toggleRemovedAction'] ?? '', $message);
    }

    /**
     * @test
     */
    public function indexAction_SetToggleRemovedVehiclesAction_ToHideRemovedVehicles_WhenQueryParamIsSet()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('foobarbaz', [$expectedQueryParam = ListVehicleController::QUERY_KEY_INCLUDE_REMOVED => '']);
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($serviceManager, ['count' => 1, 'results' => []]);

        // Execute
        $variables = (array) ($sut->indexAction($request, $routeMatch)->getVariables());

        // Assert
        $message = sprintf('Expected route reference to not have "%s" query parameter set', $expectedQueryParam);
        $notExpectedString = sprintf("%s=", $expectedQueryParam);
        $this->assertStringNotContainsString($notExpectedString, $variables['toggleRemovedAction'] ?? '', $message);
    }

    /**
     * @param string $type
     * @test
     * @depends indexAction_SetShowRemovedVehiclesToTrue_WhenALicenceHasOneRemovedVehicle
     * @dataProvider buttonTranslationKeyTypes
     */
    public function indexAction_SetToggleRemovedVehiclesActionTitle_WithRelevantMessage_WhenQueryParamIsSet_AndLicenceHasRemovedVehicles(string $type)
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $request->setQuery(new Parameters(['includeRemoved' => '']));
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($serviceManager, ['count' => 1, 'results' => []]);
        $expectedKey = sprintf('toggleRemovedVehiclesAction%s', ucfirst($type));
        $expectedTitle = sprintf('licence.vehicle.list.section.removed.action.hide-removed-vehicles.%s', $type);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $variables = (array) $result->getVariables();
        $this->assertArrayHasKey($expectedKey, $variables, sprintf('Expected result variables to have a key "%s"', $expectedKey));
        $this->assertEquals($expectedTitle, $variables[$expectedKey], sprintf('Expected result variable "%s" to have a value of "%s"', $expectedKey, $expectedTitle));
    }

    /**
     * @param string $type
     * @test
     * @depends indexAction_SetShowRemovedVehiclesToTrue_WhenALicenceHasOneRemovedVehicle
     * @dataProvider buttonTranslationKeyTypes
     */
    public function indexAction_DoesNotSetToggleRemovedVehiclesActionTitle_WhenQueryParamIsNotSet_AndLicenceDoesNotHaveRemovedVehicles(string $type)
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $request->setQuery(new Parameters(['includeRemoved' => '']));
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($serviceManager, ['count' => 0, 'results' => []]);
        $expectedKey = sprintf('toggleRemovedVehiclesAction%s', ucfirst($type));

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $variables = (array) $result->getVariables();
        $this->assertArrayNotHasKey($expectedKey, $variables, sprintf('Expected result variables to not have a key "%s"', $expectedKey));
    }

    /**
     * @return array
     */
    public function invalidInputDataProvider(): array
    {
        return [
            // $inputSetName => [$input, $expectedValidationErrorMessage]
            'sort-r query param set to invalid column' => [['sort-r' => 'foo'], 'table.validation.error.sort.in-array'],
            'sort-r query param set to empty' => [['sort-r' => ''], 'table.validation.error.sort.in-array'],
            'sort-c query param set to invalid column' => [['sort-c' => 'bar'], 'table.validation.error.sort.in-array'],
            'sort-c query param set to empty' => [['sort-c' => ''], 'table.validation.error.sort.in-array'],
            'order-r is invalid' => [['order-r' => 'foo'], 'table.validation.error.order.in-array'],
            'order-r is empty' => [['order-r' => ''], 'table.validation.error.order.in-array'],
            'order-c is invalid' => [['order-c' => 'foo'], 'table.validation.error.order.in-array'],
            'order-c is empty' => [['order-c' => ''], 'table.validation.error.order.in-array'],
        ];
    }

    /**
     * @return array
     */
    public function validInputDataProvider(): array
    {
        return [
            // $inputSetName => [$input, $expectedValidationErrorMessage]
            'sort removed vehicles table by "v.vrm" column' => [['sort-r' => 'v.vrm']],
            'sort removed vehicles table by "specifiedDate" column' => [['sort-r' => 'specifiedDate']],
            'sort removed vehicles table by "removalDate" column' => [['sort-r' => 'removalDate']],
            'sort current vehicles table by "v.vrm" column' => [['sort-c' => 'v.vrm']],
            'sort current vehicles table by "specifiedDate" column' => [['sort-c' => 'specifiedDate']],
            'order removed vehicles table descending' => [['order-r' => 'DESC']],
            'order removed vehicles table descending lowercase' => [['order-r' => 'desc']],
            'order removed vehicles table ascending' => [['order-r' => 'ASC']],
            'order removed vehicles table lowercase' => [['order-r' => 'asc']],
        ];
    }

    /**
     * @param array $input
     * @depends indexAction_IsCallable
     * @dataProvider invalidInputDataProvider
     * @test
     */
    public function indexAction_ValidatesInput_WhenInvalid_ReturnsRedirectResponse(array $input)
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('foobarbaz', $input);
        $routeMatch = new RouteMatch(['licence' => 1]);

        // Define Expectations
        $redirectHelper = $this->resolveMockService($serviceManager, Redirect::class);
        $redirectHelper->shouldReceive('refresh')->withNoArgs()->andReturn($expectedResponse = new Response())->once();

        // Execute
        $response = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertSame($expectedResponse, $response);
    }

    /**
     * @param array $input
     * @param string $expectedFlashMessage
     * @depends indexAction_IsCallable
     * @dataProvider invalidInputDataProvider
     * @test
     */
    public function indexAction_ValidatesInput_WhenInvalid_FlashesValidationMessages(array $input, string $expectedFlashMessage)
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('foobarbaz', $input);
        $routeMatch = new RouteMatch(['licence' => 8]);
        $flashMessenger = $this->resolveMockService($serviceManager, FlashMessengerHelperService::class);

        // Define Expectations
        $flashMessenger->shouldReceive('addErrorMessage')->with($expectedFlashMessage)->once();

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    /**
     * @param array $input
     * @param string $expectedFlashMessage
     * @depends indexAction_IsCallable
     * @dataProvider invalidInputDataProvider
     * @test
     */
    public function indexAction_ValidatesInput_WhenInvalid_TranslatesValidationMessages(array $input, string $expectedFlashMessage)
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('foobarbaz', $input);
        $routeMatch = new RouteMatch(['licence' => 8]);
        $translator = $this->resolveMockService($serviceManager, TranslationHelperService::class);
        $baseTranslator = $translator->getTranslator();

        // Define Expectations
        $baseTranslator->shouldReceive('translate')->with($expectedFlashMessage, IsAnything::anything())->atLeast()->once()->andReturn('');

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    /**
     * @param array $input
     * @depends indexAction_IsCallable
     * @dataProvider validInputDataProvider
     * @test
     */
    public function indexAction_ValidatesInput_WhenValid_ReturnsViewModel(array $input)
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $request->setQuery(new Parameters($input));
        $routeMatch = new RouteMatch(['licence' => 8]);

        // Execute
        $response = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $response);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsRemovedVehiclesTable_ExcludingActiveVehicles()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $queryHandler = $this->resolveMockService($serviceManager, HandleQuery::class);

        // Define Expectations
        $queryHandler->shouldReceive('__invoke')->withArgs(function ($query) {
            return $query instanceof Vehicles && $query->getIncludeActive() === false;
        })->once()->andReturns($this->setUpQueryResponse());

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsRemovedVehiclesTable_SortedByDefault()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $queryHandler = $this->resolveMockService($serviceManager, HandleQuery::class);

        // Define Expectations
        $queryHandler->shouldReceive('__invoke')->withArgs(function ($query) {
            return $query instanceof Vehicles && $query->getIncludeRemoved() === true && $query->getSort() === 'specifiedDate';
        })->once()->andReturns($this->setUpQueryResponse());

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsRemovedVehiclesTable_OrderedByDefault()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $queryHandler = $this->resolveMockService($serviceManager, HandleQuery::class);

        // Define Expectations
        $queryHandler->shouldReceive('__invoke')->withArgs(function ($query) {
            return $query instanceof Vehicles && $query->getIncludeRemoved() === true && $query->getOrder() === 'DESC';
        })->once()->andReturns($this->setUpQueryResponse());

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsRemovedVehiclesTable_LimitsTo10ByDefault()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $queryHandler = $this->resolveMockService($serviceManager, HandleQuery::class);

        // Define Expectations
        $queryHandler->shouldReceive('__invoke')->withArgs(function ($query) {
            return $query instanceof Vehicles && $query->getIncludeRemoved() === true && $query->getLimit() === 10;
        })->once()->andReturns($this->setUpQueryResponse());

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsRemovedVehiclesTable_SetsPageToFirstPageByDefault()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $queryHandler = $this->resolveMockService($serviceManager, HandleQuery::class);

        // Define Expectations
        $queryHandler->shouldReceive('__invoke')->withArgs(function ($query) {
            return $query instanceof Vehicles && $query->getIncludeRemoved() === true && $query->getPage() === 1;
        })->once()->andReturns($this->setUpQueryResponse());

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
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $commandHandler = $this->resolveMockService($serviceManager, HandleCommand::class);
        $formHelper = $this->resolveMockService($serviceManager, FormHelperService::class);
        $mockForm = $this->setUpForm();
        $mockForm->shouldReceive('getData')->andReturn(['ocrsCheckbox' => $expected = 'Y']);
        $formHelper->shouldReceive('createForm')->andReturn($mockForm);

        // Define Expectations
        $commandHandler
            ->shouldReceive('__invoke')
            ->withArgs(function ($command) use ($expected) {
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
        $request = $this->setUpRequest('/');
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

    /**
     * @test
     */
    public function indexAction_HidesRemovedVehicles_WhenSearching_AndLicenceHasRemovedVehicles()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = $this->setUpRequest('/foo/bar');
        $request->setQuery(new Parameters([ListVehicleSearch::FIELD_VEHICLE_SEARCH => [AbstractInputSearch::ELEMENT_INPUT_NAME => 'foo']]));
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($serviceManager, ['count' => 1, ['results' => []]]);

        // Execute
        $result = (array) $sut->indexAction($request, $routeMatch)->getVariables();

        // Assert
        $this->assertArrayNotHasKey('showRemovedVehicles', $result);
    }

    /**
     * Sets up default services.
     *
     * @return array
     */
    public function setUpDefaultServices()
    {
        return [
            TableFactory::class => $this->setUpTableFactory(),
            TranslationHelperService::class => $this->setUpTranslator(),
            ResponseHelperService::class => $this->setUpResponseHelper(),
            FormHelperService::class => $this->setUpFormHelper(),
            FlashMessengerHelperService::class => $this->setUpFlashMessenger(),
            HandleCommand::class => $this->setUpCommandHandler(),
            HandleQuery::class => $this->setUpQueryHandler(),
            Url::class => $this->setUpUrlHelper(),
            Redirect::class => $this->setUpRedirectHelper(),
        ];
    }

    /**
     * @param ServiceManager $serviceManager
     * @return ListVehicleController
     */
    protected function setUpSut(ServiceManager $serviceManager)
    {
        $factory = new ListVehicleControllerFactory();
        $dispatcher = $factory->createService($serviceManager);
        return $dispatcher->getDelegate();
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
        $instance->shouldReceive('__invoke')->andReturn($this->setUpQueryResponse())->byDefault();
        $instance->shouldReceive('__invoke')->with(IsInstanceOf::anInstanceOf(Licence::class))->andReturnUsing(function ($query) {
            $licenceData = $this->setUpDefaultLicenceData();
            $licenceData['id'] = $query->getId();
            return $this->setUpQueryResponse($licenceData);
        })->byDefault();
        return $instance;
    }

    /**
     * @return array
     */
    public function setUpDefaultLicenceData(): array
    {
        return [
            'id' => 1,
            'licNo' => 'OB1234567',
            'organisation' => [
                'confirmShareVehicleInfo' => 'N',
            ],
        ];
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
        $instance->shouldReceive('prepareTable', 'getTableBuilder')->andReturnUsing(function () {
            return $this->setUpTableBuilder();
        })->byDefault();
        return $instance;
    }

    /**
     * @param mixed $data
     * @return QueryResponse|MockInterface
     */
    protected function setUpQueryResponse($data = ['count' => 0, 'results' => []]): QueryResponse
    {
        $response = m::mock(QueryResponse::class);
        $response->shouldIgnoreMissing();
        $response->shouldReceive('getResult')->andReturn($data)->byDefault();
        return $response;
    }

    /**
     * @return MockInterface
     */
    public function setUpTableBuilder(): MockInterface
    {
        $tableBuilder = m::mock(TableBuilder::class);
        $tableBuilder->shouldIgnoreMissing($tableBuilder);
        $tableBuilder->shouldReceive('getSettings')->andReturn([])->byDefault();
        $tableBuilder->shouldReceive('getTotal')->andReturn(0)->byDefault();
        $tableBuilder->shouldReceive('getLimit')->andReturn(9)->byDefault();
        return $tableBuilder;
    }

    /**
     * @return MockInterface
     */
    protected function setUpTranslator(): MockInterface
    {
        $instance = m::mock(TranslationHelperService::class);
        $instance->shouldIgnoreMissing('');
        $instance->shouldReceive('translate')->andReturnUsing(function ($val) {
            return $val;
        })->byDefault();
        $instance->shouldReceive('translateReplace')->andReturnUsing(function ($message, $params) {
            return $message . ':' . json_encode($params);
        })->byDefault();

        $baseTranslator = m::mock(TranslatorInterface::class);
        $baseTranslator->shouldReceive('translate')->andReturnUsing(function ($val) {
            return $val;
        })->byDefault();
        $instance->shouldReceive('getTranslator')->andReturn($baseTranslator)->byDefault();

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

        // Mock search form by default
        $searchForm = $this->setUpForm();
        $any = IsAnything::anything();
        $instance->shouldReceive('createForm')->with(ListVehicleSearch::class, $any, $any)->andReturn($searchForm)->byDefault();


        return $instance;
    }

    /**
     * @return MockInterface|Form
     */
    protected function setUpForm(): MockInterface
    {
        $form = m::mock(Form::class);
        $form->shouldIgnoreMissing();
        $form->shouldReceive('get')->andReturnUsing(function () {
            $mockFormElement = m::mock();
            $mockFormElement->shouldIgnoreMissing();
            $mockFormElement->shouldReceive('setOption')->andReturnSelf()->byDefault();
            return $mockFormElement;
        })->byDefault();
        $form->shouldReceive('isValid')->andReturnTrue()->byDefault();
        return $form;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $tableName
     * @param null $data
     * @param null $params
     * @return MockInterface
     */
    protected function expectTableToBePrepared(ServiceLocatorInterface $serviceLocator, string $tableName, $data = null, $params = null): MockInterface
    {
        $any = IsAnything::anything();
        $tableBuilder = $this->setUpTableBuilder();
        $tableFactory = $this->resolveMockService($serviceLocator, TableFactory::class);
        $tableFactory->shouldReceive('prepareTable')->with($tableName, $data ?? $any, $params ?? $any)->once()->andReturn($tableBuilder);
        return $tableBuilder;
    }

    /**
     * Resolves a mock service from a service container.
     *
     * @param ServiceLocatorInterface $serviceManager
     * @return MockInterface
     */
    protected function resolveMockService(ServiceLocatorInterface $serviceManager, string $service): MockInterface
    {
        $service = $serviceManager->get($service);
        assert($service instanceof MockInterface, 'Expected instance of MockInterface');
        return $service;
    }

    /**
     * @return MockInterface|FlashMessengerHelperService
     */
    protected function setUpFlashMessenger(): MockInterface
    {
        $messenger = m::mock(FlashMessengerHelperService::class);
        $messenger->shouldIgnoreMissing('');
        return $messenger;
    }

    /**
     * @param string $url
     * @param array|null $input
     * @return Request
     */
    protected function setUpRequest(string $url, array $input = null)
    {
        $uri = m::mock(Http::class);
        $uri->shouldIgnoreMissing($uri);
        $uri->shouldReceive('toString')->andReturn($url ?? 'foobarbaz');

        $request = new Request();
        $request->setUri($uri);
        $request->setQuery(new Parameters($input ?? []));

        return $request;
    }

    /**
     * @return MockInterface|Redirect
     */
    protected function setUpRedirectHelper(): MockInterface
    {
        $instance = m::mock(Redirect::class);
        $instance->shouldIgnoreMissing(new Response());
        return $instance;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array $queryResultData
     */
    protected function injectRemovedVehiclesQueryResultData(ServiceLocatorInterface $serviceLocator, array $queryResultData)
    {
        $removedVehiclesQueryResponse = $this->setUpQueryResponse($queryResultData);
        $queryHandler = $this->resolveMockService($serviceLocator, HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')->withArgs(function ($query) {
            return $query instanceof Vehicles && $query->getIncludeActive() === false;
        })->andReturns($removedVehiclesQueryResponse)->byDefault();
    }
}
