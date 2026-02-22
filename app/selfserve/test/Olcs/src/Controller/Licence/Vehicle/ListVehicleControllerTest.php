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
use Dvsa\Olcs\Transfer\Command\Licence\UpdateVehicles;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\Licence\Vehicles;
use Hamcrest\Arrays\IsArrayContainingKeyValuePair;
use Hamcrest\Core\IsAnything;
use Hamcrest\Core\IsIdentical;
use Hamcrest\Arrays\IsArrayContainingKey;
use Hamcrest\Core\IsInstanceOf;
use Interop\Container\Containerinterface;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Parameters;
use Laminas\Uri\Http;
use Laminas\Validator\Translator\TranslatorInterface;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Olcs\Controller\Licence\Vehicle\ListVehicleController;
use Olcs\Form\Model\Form\Vehicle\ListVehicleSearch;
use Olcs\Table\TableEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;

/**
 * @see ListVehicleController
 */
class ListVehicleControllerTest extends MockeryTestCase
{
    protected const ROUTE_CONFIGURATION_FOR_LICENCE_WITH_REMOVED_VEHICLES_SHOWING_AND_FOCUSED = [
        'licence/vehicle/list/GET',
        [
            'licence' => 0,
        ],
        [
            'query' => [
                'includeRemoved' => '',
            ],
            'fragment' => ListVehicleController::REMOVE_TABLE_WRAPPER_ID,
        ],
    ];
    protected const ROUTE_CONFIGURATION_FOR_LICENCE_WITHOUT_REMOVED_VEHICLES_SHOWING = [
        'licence/vehicle/list/GET',
        [
            'licence' => 0,
        ],
        [
            'query' => [
            ],
        ],
    ];
    protected const A_URL = 'A URL';

    /**
     * @var ListVehicleController|null
     */
    protected $sut;

    /**
     * @var HandleCommand
     */
    protected $commandHandlerMock;

    /**
     * @var HandleQuery
     */
    protected $queryHandlerMock;

    /**
     * @var TranslationHelperService
     */
    protected $translatorMock;

    /**
     * @var Url
     */
    protected $urlHelperMock;

    /**
     * @var ResponseHelperService
     */
    protected $responseHelperMock;

    /**
     * @var TableFactory
     */
    protected $tableFactoryMock;

    /**
     * @var FormHelperService
     */
    protected $formHelperMock;

    /**
     * @var FlashMessengerHelperService
     */
    protected $flashMessengerMock;

    /**
     * @var Redirect
     */
    protected $redirectHelperMock;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocatorMock;

    #[Test]
    public function indexActionIsCallable(): void
    {
        $this->setUpSut();
        // Assert
        $this->assertIsCallable([$this->sut, 'indexAction']);
    }

    #[Test]
    public function postActionIsCallable(): void
    {
        $this->setUpSut();
        // Assert
        $this->assertIsCallable([$this->sut, 'postAction']);
    }

    #[Test]
    public function indexActionRespondsInHtmlFormatWhenNoFormatIsProvided(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);

        $this->urlHelperMock->shouldReceive('fromRoute')->andReturn('licence/vehicle/list/GET');

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    #[Test]
    public function indexActionRespondsInHtmlFormatWhenHtmlFormatIsProvided(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $request->setQuery(new Parameters(['format' => ListVehicleController::FORMAT_HTML]));
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    #[Test]
    public function indexActionRespondsInHtmlFormatWithLicence(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);

        assert($this->queryHandlerMock instanceof MockInterface, 'Expected instance of MockInterface');
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceQueryMatcher = IsInstanceOf::anInstanceOf(Licence::class);
        $licenceQueryResponse = m::mock(QueryResponse::class);
        $licenceQueryResponse->shouldIgnoreMissing();
        $licenceQueryResponse->shouldReceive('getResult')->andReturn($licenceData);
        $this->queryHandlerMock->shouldReceive('__invoke')->with($licenceQueryMatcher)->andReturn($licenceQueryResponse);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertSame($licenceData, $result->getVariables()['licence'] ?? null);
    }

    #[Depends('indexActionRespondsInHtmlFormatWhenHtmlFormatIsProvided')]
    #[Test]
    public function indexActionRespondsInHtmlFormatWithExportCurrentAndRemovedCsvAction(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertArrayHasKey('exportCurrentAndRemovedCsvAction', $result->getVariables());
    }

    #[Depends('indexActionRespondsInHtmlFormatWhenHtmlFormatIsProvided')]
    #[Test]
    public function indexActionRespondsInHtmlFormatWithExportCurrentAndRemovedCsvActionWithFormatQueryParameter(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $licenceId = 1;
        $routeMatch = new RouteMatch($routeParams = ['licence' => $licenceId]);
        $expectedUrl = "abcdefg";

        // Define Expectations
        $queryMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('format', 'csv');
        $optionsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('query', $queryMatcher);
        $this->urlHelperMock->shouldReceive('fromRoute')->with('licence/vehicle/list/GET', $routeParams, $optionsMatcher)->andReturn($expectedUrl);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertEquals($expectedUrl, $result->getVariables()['exportCurrentAndRemovedCsvAction']);
    }

    #[Test]
    public function indexActionRespondsInHtmlFormatWithExportCurrentAndRemovedCsvActionWithIncludeRemovedQueryParameter(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $licenceId = 1;
        $routeMatch = new RouteMatch($routeParams = ['licence' => $licenceId]);
        $expectedUrl = "abcdefg";

        // Define Expectations
        $queryMatcher = IsArrayContainingKey::hasKeyInArray('includeRemoved');
        $optionsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('query', $queryMatcher);
        $this->urlHelperMock->shouldReceive('fromRoute')->with('licence/vehicle/list/GET', $routeParams, $optionsMatcher)->andReturn($expectedUrl);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertEquals($expectedUrl, $result->getVariables()['exportCurrentAndRemovedCsvAction']);
    }

    #[Test]
    public function indexActionRespondsInHtmlFormatAndConfiguresCurrentVehicleTableQuery(): void
    {
        // Setup
        $this->setUpSut();

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

        $this->expectTableToBePrepared($this->serviceLocatorMock, TableEnum::LICENCE_VEHICLE_LIST_CURRENT, null, $paramsMatcher);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);
        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    #[Test]
    public function indexActionRespondsInHtmlFormatAndConfiguresCurrentVehicleTablePageWhenNoPageIsSetOnARequest(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $paramsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('page', 1);
        $this->expectTableToBePrepared($this->serviceLocatorMock, TableEnum::LICENCE_VEHICLE_LIST_CURRENT, null, $paramsMatcher);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    #[Test]
    public function indexActionRespondsInHtmlFormatAndConfiguresCurrentVehicleTablePageWhenEmptyPageIsSetOnARequest(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $request->setQuery(new Parameters(['page' => '']));
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $paramsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('page', 1);
        $this->expectTableToBePrepared($this->serviceLocatorMock, TableEnum::LICENCE_VEHICLE_LIST_CURRENT, null, $paramsMatcher);

        // Execute
        $this->sut->indexAction($request, $routeMatch);
    }

    /**
     * @return (int|string)[][]
     *
     * @psalm-return array{'title when table total not equal to one': list{2, 'licence.vehicle.list.section.removed.header.title.plural'}, 'title when table total is one': list{1, 'licence.vehicle.list.section.removed.header.title.singular'}}
     */
    public static function setUpRemovedTableTitleData(): array
    {
        return [
            'title when table total not equal to one' => [2, 'licence.vehicle.list.section.removed.header.title.plural'],
            'title when table total is one' => [1, 'licence.vehicle.list.section.removed.header.title.singular'],
        ];
    }

    #[DataProvider('setUpRemovedTableTitleData')]
    #[Test]
    public function indexActionRespondsInHtmlFormatWithCorrectRemovedVehicleTableTitle(int $total, string $expectedTranslationKey): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $request->setQuery($this->parametersWhichIncludeRemoved());
        $routeMatch = new RouteMatch([]);
        $expectedTitle = 'foo';
        $results = array_fill(0, $total, ['id' => 6]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => $total, 'results' => $results]);

        // Define Expectations
        $this->translatorMock->shouldReceive('translateReplace')->once()->with($expectedTranslationKey, [count($results)])->andReturn($expectedTitle);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);
        $title = $result->getVariable('removedVehicleTableTitle');

        // Assert
        $this->assertEquals($expectedTitle, $title);
    }

    #[Depends('indexActionRespondsInHtmlFormatWhenHtmlFormatIsProvided')]
    #[Test]
    public function indexActionSetShowRemovedVehiclesToFalseWhenALicenceHasNoRemovedVehicles(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 0, 'results' => []]);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $matcher = IsArrayContainingKeyValuePair::hasKeyValuePair('showRemovedVehicles', false);
        $this->assertTrue($matcher->matches((array) $result->getVariables()), 'Expected result variables to have a key "showRemovedVehicles" with a value of false');
    }

    #[Depends('indexActionRespondsInHtmlFormatWhenHtmlFormatIsProvided')]
    #[Test]
    public function indexActionSetShowRemovedVehiclesToTrueWhenALicenceHasOneRemovedVehicle(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 1, 'results' => []]);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $matcher = IsArrayContainingKeyValuePair::hasKeyValuePair('showRemovedVehicles', true);
        $this->assertTrue($matcher->matches((array) $result->getVariables()), 'Expected result variables to have a key "showRemovedVehicles" with a value of true');
    }

    #[Depends('indexActionSetShowRemovedVehiclesToTrueWhenALicenceHasOneRemovedVehicle')]
    #[Test]
    public function indexActionSetsExpandRemovedVehiclesWhenQueryParamIsSetAndALicenceHasOneRemovedVehicle(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $request->setQuery($this->parametersWhichIncludeRemoved());
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 1, 'results' => []]);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertTrue($result->getVariable('showRemovedVehiclesExpanded'));
    }

    #[Depends('indexActionSetShowRemovedVehiclesToTrueWhenALicenceHasOneRemovedVehicle')]
    #[Test]
    public function indexActionDoesNotSetExpandRemovedVehiclesWhenQueryParamIsSetAndThereAreNoRemovedVehicles(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $request->setQuery($this->parametersWhichIncludeRemoved());
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 0, 'results' => []]);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertArrayNotHasKey('showRemovedVehiclesExpanded', $result->getVariables());
    }

    #[Test]
    public function indexActionToggleUrlIncludesFragmentWhenQueryParamIsNotSetAndALicenceHasOneRemovedVehicle(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 1, 'results' => []]);
        $this->urlHelperMock
            ->allows('fromRoute')
            ->with(...static::ROUTE_CONFIGURATION_FOR_LICENCE_WITH_REMOVED_VEHICLES_SHOWING_AND_FOCUSED)
            ->andReturn(static::A_URL);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->urlHelperMock->shouldHaveReceived('fromRoute')->withArgs(static::ROUTE_CONFIGURATION_FOR_LICENCE_WITH_REMOVED_VEHICLES_SHOWING_AND_FOCUSED);
        $this->assertEquals(static::A_URL, $result->getVariable('toggleRemovedAction'));
    }

    #[Depends('indexActionIsCallable')]
    #[Test]
    public function indexActionToggleUrlDoesNotIncludeFragmentWhenQueryParamIsSetAndALicenceHasOneRemovedVehicle(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $request->setQuery($this->parametersWhichIncludeRemoved());
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 1, 'results' => []]);
        $this->urlHelperMock
            ->allows('fromRoute')
            ->with(...static::ROUTE_CONFIGURATION_FOR_LICENCE_WITHOUT_REMOVED_VEHICLES_SHOWING)
            ->andReturn(static::A_URL);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->urlHelperMock->shouldHaveReceived('fromRoute')->withArgs(static::ROUTE_CONFIGURATION_FOR_LICENCE_WITHOUT_REMOVED_VEHICLES_SHOWING);
        $this->assertEquals(static::A_URL, $result->getVariable('toggleRemovedAction'));
    }

    /**
     * @return array
     */
    public static function buttonTranslationKeyTypes(): array
    {
        return [
            'title' => ['title'],
            'label' => ['label'],
        ];
    }

    #[Test]
    public function indexActionDoesNotSetToggleRemovedVehiclesActionWhenNoRemovedVehicles(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('foobarbaz');
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 0, 'results' => []]);

        // Execute
        $variables = (array) ($this->sut->indexAction($request, $routeMatch)->getVariables());

        // Assert
        $this->assertArrayNotHasKey('toggleRemovedAction', $variables);
    }

    #[Test]
    public function indexActionDoesNotSetToggleRemovedVehiclesActionWhenSearching(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('foobarbaz', ['vehicleSearch' => []]);
        $routeMatch = new RouteMatch([]);

        // Execute
        $variables = (array) ($this->sut->indexAction($request, $routeMatch)->getVariables());

        // Assert
        $this->assertArrayNotHasKey('toggleRemovedAction', $variables);
    }

    #[Test]
    public function indexActionSetToggleRemovedVehiclesActionToShowRemovedVehiclesWhenNoQueryParamIsSet(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('foobarbaz');
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 1, 'results' => []]);

        // Execute
        $variables = (array) ($this->sut->indexAction($request, $routeMatch)->getVariables());

        // Assert
        $expectedQueryParam = ListVehicleController::QUERY_KEY_INCLUDE_REMOVED;
        $message = sprintf('Expected route reference to have "%s" query parameter set', $expectedQueryParam);
        $notExpectedString = sprintf("%s=", $expectedQueryParam);
        $this->assertStringNotContainsString($notExpectedString, $variables['toggleRemovedAction'] ?? '', $message);
    }

    #[Test]
    public function indexActionSetToggleRemovedVehiclesActionToHideRemovedVehiclesWhenQueryParamIsSet(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('foobarbaz', [$expectedQueryParam = ListVehicleController::QUERY_KEY_INCLUDE_REMOVED => '']);
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 1, 'results' => []]);

        // Execute
        $variables = (array) ($this->sut->indexAction($request, $routeMatch)->getVariables());

        // Assert
        $message = sprintf('Expected route reference to not have "%s" query parameter set', $expectedQueryParam);
        $notExpectedString = sprintf("%s=", $expectedQueryParam);
        $this->assertStringNotContainsString($notExpectedString, $variables['toggleRemovedAction'] ?? '', $message);
    }

    #[DataProvider('buttonTranslationKeyTypes')]
    #[Test]
    public function indexActionSetToggleRemovedVehiclesActionTitleWithRelevantMessageWhenQueryParamIsSetAndLicenceHasRemovedVehicles(string $type): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $request->setQuery($this->parametersWhichIncludeRemoved());
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 1, 'results' => []]);
        $expectedKey = sprintf('toggleRemovedVehiclesAction%s', ucfirst($type));
        $expectedTitle = sprintf('licence.vehicle.list.section.removed.action.hide-removed-vehicles.%s', $type);

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $variables = (array) $result->getVariables();
        $this->assertArrayHasKey($expectedKey, $variables, sprintf('Expected result variables to have a key "%s"', $expectedKey));
        $this->assertEquals($expectedTitle, $variables[$expectedKey], sprintf('Expected result variable "%s" to have a value of "%s"', $expectedKey, $expectedTitle));
    }

    #[DataProvider('buttonTranslationKeyTypes')]
    #[Test]
    public function indexActionDoesNotSetToggleRemovedVehiclesActionTitleWhenQueryParamIsNotSetAndLicenceDoesNotHaveRemovedVehicles(string $type): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $request->setQuery($this->parametersWhichIncludeRemoved());
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 0, 'results' => []]);
        $expectedKey = sprintf('toggleRemovedVehiclesAction%s', ucfirst($type));

        // Execute
        $result = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $variables = (array) $result->getVariables();
        $this->assertArrayNotHasKey($expectedKey, $variables, sprintf('Expected result variables to not have a key "%s"', $expectedKey));
    }

    public static function invalidInputDataNoMessageProvider(): array
    {
        return [
            'sort-r query param set to invalid column' => [['sort-r' => 'foo']],
            'sort-r query param set to empty' => [['sort-r' => '']],
            'sort-c query param set to invalid column' => [['sort-c' => 'bar']],
            'sort-c query param set to empty' => [['sort-c' => '']],
            'order-r is invalid' => [['order-r' => 'foo']],
            'order-r is empty' => [['order-r' => '']],
            'order-c is invalid' => [['order-c' => 'foo']],
            'order-c is empty' => [['order-c' => '']],
        ];
    }

    public static function invalidInputDataProvider(): array
    {
        return [
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
    public static function validInputDataProvider(): array
    {
        return [
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

    #[Depends('indexActionIsCallable')]
    #[DataProvider('invalidInputDataNoMessageProvider')]
    #[Test]
    public function indexActionValidatesInputWhenInvalidReturnsRedirectResponse(array $input): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('foobarbaz', $input);
        $routeMatch = new RouteMatch(['licence' => 1]);
        // Define Expectations
        $this->flashMessengerMock->shouldReceive('addErrorMessage')->withAnyArgs()->once();
        // Define Expectations
        $this->redirectHelperMock->shouldReceive('refresh')->withNoArgs()->andReturn($expectedResponse = new Response())->once();

        // Execute
        $response = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertSame($expectedResponse, $response);
    }

    #[Depends('indexActionIsCallable')]
    #[DataProvider('invalidInputDataProvider')]
    #[Test]
    public function indexActionValidatesInputWhenInvalidFlashesValidationMessages(array $input, string $expectedFlashMessage): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('foobarbaz', $input);
        $routeMatch = new RouteMatch(['licence' => 8]);

        // Define Expectations
        $this->flashMessengerMock->shouldReceive('addErrorMessage')->with($expectedFlashMessage)->once();
        $this->redirectHelperMock->shouldReceive('refresh')->withNoArgs()->andReturn($expectedResponse = new Response())->once();

        // Execute
        $this->sut->indexAction($request, $routeMatch);
    }

    #[Depends('indexActionIsCallable')]
    #[DataProvider('invalidInputDataProvider')]
    #[Test]
    public function indexActionValidatesInputWhenInvalidTranslatesValidationMessages(array $input, string $expectedFlashMessage): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('foobarbaz', $input);
        $routeMatch = new RouteMatch(['licence' => 8]);
        $baseTranslator = $this->translatorMock->getTranslator();

        // Define Expectations
        $baseTranslator->shouldReceive('translate')->with($expectedFlashMessage, IsAnything::anything())->atLeast()->once()->andReturn('');
        $this->redirectHelperMock->shouldReceive('refresh')->withNoArgs()->andReturn($expectedResponse = new Response())->once();
        $this->flashMessengerMock->shouldReceive('addErrorMessage')->withAnyArgs()->once();
        // Execute
        $this->sut->indexAction($request, $routeMatch);
    }

    #[Depends('indexActionIsCallable')]
    #[DataProvider('validInputDataProvider')]
    #[Test]
    public function indexActionValidatesInputWhenValidReturnsViewModel(array $input): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $request->setQuery(new Parameters($input));
        $routeMatch = new RouteMatch(['licence' => 8]);

        // Execute
        $response = $this->sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $response);
    }

    #[Test]
    public function indexActionReturnsRemovedVehiclesTableExcludingActiveVehicles(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $this->queryHandlerMock->shouldReceive('__invoke')->withArgs(fn($query) => $query instanceof Vehicles && $query->getIncludeActive() === false)->once()->andReturns($this->setUpQueryResponse());

        // Execute
        $this->sut->indexAction($request, $routeMatch);
    }

    #[Test]
    public function indexActionReturnsRemovedVehiclesTableSortedByDefault(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $this->queryHandlerMock->shouldReceive('__invoke')->withArgs(fn($query) => $query instanceof Vehicles && $query->getIncludeRemoved() === true && $query->getSort() === 'removalDate')->once()->andReturns($this->setUpQueryResponse());

        // Execute
        $this->sut->indexAction($request, $routeMatch);
    }

    #[Test]
    public function indexActionReturnsRemovedVehiclesTableOrderedByDefault(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $this->queryHandlerMock->shouldReceive('__invoke')->withArgs(fn($query) => $query instanceof Vehicles && $query->getIncludeRemoved() === true && $query->getOrder() === 'DESC')->once()->andReturns($this->setUpQueryResponse());

        // Execute
        $this->sut->indexAction($request, $routeMatch);
    }

    #[Test]
    public function indexActionReturnsRemovedVehiclesTableLimitsTo10ByDefault(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $this->queryHandlerMock->shouldReceive('__invoke')->withArgs(fn($query) => $query instanceof Vehicles && $query->getIncludeRemoved() === true && $query->getLimit() === 10)->once()->andReturns($this->setUpQueryResponse());

        // Execute
        $this->sut->indexAction($request, $routeMatch);
    }

    #[Test]
    public function indexActionReturnsRemovedVehiclesTableSetsPageToFirstPageByDefault(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $this->queryHandlerMock->shouldReceive('__invoke')->withArgs(fn($query) => $query instanceof Vehicles && $query->getIncludeRemoved() === true && $query->getPage() === 1)->once()->andReturns($this->setUpQueryResponse());

        // Execute
        $this->sut->indexAction($request, $routeMatch);
    }

    #[Test]
    public function postActionRespondsInHtmlFormatSetsUserOCRSOptInPreferenceCheckboxValidValuesRunsCommand(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $mockForm = $this->setUpForm();
        $mockForm->shouldReceive('getData')->andReturn(['ocrsCheckbox' => $expected = 'Y']);
        $this->formHelperMock->shouldReceive('createForm')->andReturn($mockForm);

        // Define Expectations
        $this->commandHandlerMock
            ->shouldReceive('__invoke')
            ->withArgs(fn($command) => $command instanceof UpdateVehicles && $command->getShareInfo() === $expected)
            ->once()
            ->andReturn(null);

        // Execute
        $this->sut->postAction($request, $routeMatch);
    }

    #[Test]
    public function postActionRespondsInHtmlFormatSetsUserOCRSOptInPreferenceCheckboxInvalidValuesReturnsIndexActionWithErrors(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/');
        $routeMatch = new RouteMatch([]);
        $mockForm = $this->setUpForm();
        $mockForm->shouldReceive('isValid')->andReturnFalse();
        $this->formHelperMock->shouldReceive('createForm')->andReturn($mockForm);

        // Define Expectations
        $updateVehicleCommandMatcher = IsInstanceOf::anInstanceOf(UpdateVehicles::class);
        $this->commandHandlerMock->shouldReceive('__invoke')->with($updateVehicleCommandMatcher)->never();

        // Execute
        $this->sut->postAction($request, $routeMatch);
    }


    #[Test]
    public function indexActionHidesRemovedVehiclesWhenSearchingAndLicenceHasRemovedVehicles(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->setUpRequest('/foo/bar');
        $request->setQuery(new Parameters([ListVehicleSearch::FIELD_VEHICLE_SEARCH => [AbstractInputSearch::ELEMENT_INPUT_NAME => 'foo']]));
        $routeMatch = new RouteMatch([]);
        $this->injectRemovedVehiclesQueryResultData($this->serviceLocatorMock, ['count' => 1, ['results' => []]]);

        // Execute
        $result = (array) $this->sut->indexAction($request, $routeMatch)->getVariables();

        // Assert
        $this->assertArrayNotHasKey('showRemovedVehicles', $result);
    }

    protected function setUp(): void
    {
        $this->commandHandlerMock = m::mock(HandleCommand::class);
        $this->queryHandlerMock = m::mock(HandleQuery::class);
        $this->translatorMock = m::mock(TranslationHelperService::class);
        $this->urlHelperMock = m::mock(Url::class);
        $this->responseHelperMock = m::mock(ResponseHelperService::class);
        $this->tableFactoryMock = m::mock(TableFactory::class);
        $this->formHelperMock = m::mock(FormHelperService::class);
        $this->flashMessengerMock = m::mock(FlashMessengerHelperService::class);
        $this->redirectHelperMock = m::mock(Redirect::class);
        $this->serviceLocatorMock = m::mock(ServiceLocatorInterface::class);
    }

    protected function setUpSut(): void
    {
        $this->setUpTranslator();
        $this->setUpQueryHandler();
        $this->setUpTableFactory();
        $this->setUpFormHelper();
        $this->urlHelper();

        $this->sut = new ListVehicleController(
            $this->commandHandlerMock,
            $this->queryHandlerMock,
            $this->translatorMock,
            $this->urlHelperMock,
            $this->responseHelperMock,
            $this->tableFactoryMock,
            $this->formHelperMock,
            $this->flashMessengerMock,
            $this->redirectHelperMock
        );
    }

    protected function setUpQueryHandler(): void
    {
        $instance = m::mock(HandleQuery::class);
        $this->queryHandlerMock->shouldIgnoreMissing();
        $this->queryHandlerMock->shouldReceive('__invoke')->andReturn($this->setUpQueryResponse())->byDefault();
        $this->queryHandlerMock->shouldReceive('__invoke')->with(IsInstanceOf::anInstanceOf(Licence::class))->andReturnUsing(function ($query) {
            $licenceData = $this->setUpDefaultLicenceData();
            $licenceData['id'] = $query->getId();
            return $this->setUpQueryResponse($licenceData);
        })->byDefault();
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

    protected function setUpCommandHandler(): m\LegacyMockInterface
    {
        $instance = m::mock(HandleCommand::class);
        $instance->shouldIgnoreMissing();

        $response = m::mock(Response::class);
        $response->shouldIgnoreMissing();
        $instance->shouldReceive('__invoke')->andReturn($response)->byDefault();

        return $instance;
    }


    protected function setUpTableFactory(): void
    {
        $instance = m::mock(TableFactory::class);
        $this->tableFactoryMock->shouldIgnoreMissing();
        $this->tableFactoryMock->shouldReceive('prepareTable', 'getTableBuilder')->andReturnUsing(fn() => $this->setUpTableBuilder())->byDefault();
    }

    protected function setUpQueryResponse(mixed $data = ['count' => 0, 'results' => []]): QueryResponse
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

    protected function setUpTranslator(): void
    {
        $this->translatorMock->shouldIgnoreMissing('');
        $this->translatorMock->shouldReceive('translate')->andReturnUsing(fn($val) => $val)->byDefault();
        $this->translatorMock->shouldReceive('translateReplace')->andReturnUsing(fn($message, $params) => $message . ':' . json_encode($params))->byDefault();

        $baseTranslator = m::mock(TranslatorInterface::class);
        $baseTranslator->shouldReceive('translate')->andReturnUsing(fn($val) => $val)->byDefault();
        $this->translatorMock->shouldReceive('getTranslator')->andReturn($baseTranslator)->byDefault();
    }


    protected function urlHelper(): void
    {
        $this->urlHelperMock->shouldIgnoreMissing('');
    }

    protected function setUpResponseHelper(): m\LegacyMockInterface
    {
        $instance = m::mock(ResponseHelperService::class);
        $instance->shouldIgnoreMissing();
        return $instance;
    }

    protected function setUpFormHelper(): void
    {
        $this->formHelperMock->shouldIgnoreMissing();

        $mockForm = $this->setUpForm();
        $this->formHelperMock->shouldReceive('createForm')->andReturn($mockForm)->byDefault();

        // Mock search form by default
        $searchForm = $this->setUpForm();
        $any = IsAnything::anything();
        $this->formHelperMock->shouldReceive('createForm')->with(ListVehicleSearch::class, $any, $any)->andReturn($searchForm)->byDefault();
    }

    protected function setUpForm(): MockInterface
    {
        $form = m::mock(Form::class);
        $form->shouldIgnoreMissing();
        $form->shouldReceive('get')->andReturnUsing(function () {
            $mockFormElement = m::mock(ElementInterface::class);
            $mockFormElement->shouldIgnoreMissing();
            $mockFormElement->shouldReceive('setOption')->andReturnSelf()->byDefault();
            return $mockFormElement;
        })->byDefault();
        $form->shouldReceive('isValid')->andReturnTrue()->byDefault();
        return $form;
    }

    /**
     * @param null $data
     * @param null $params
     * @return MockInterface
     */
    protected function expectTableToBePrepared(ServiceLocatorInterface $serviceLocator, string $tableName, mixed $data = null, mixed $params = null): MockInterface
    {
        $any = IsAnything::anything();
        $tableBuilder = $this->setUpTableBuilder();
        $this->tableFactoryMock->shouldReceive('prepareTable')->with($tableName, $data ?? $any, $params ?? $any)->once()->andReturn($tableBuilder);
        return $tableBuilder;
    }

    protected function setUpFlashMessenger(): MockInterface
    {
        $messenger = m::mock(FlashMessengerHelperService::class);
        $messenger->shouldIgnoreMissing('');
        return $messenger;
    }

    /**
     * @param array|null $input
     * @return Request
     */
    protected function setUpRequest(string $url, ?array $input = null): Request
    {
        $uri = m::mock(Http::class);
        $uri->shouldIgnoreMissing($uri);
        $uri->shouldReceive('toString')->andReturn($url ?? 'foobarbaz');

        $request = new Request();
        $request->setUri($uri);
        $request->setQuery(new Parameters($input ?? []));

        return $request;
    }

    protected function setUpRedirectHelper(): MockInterface
    {
        $instance = m::mock(Redirect::class);
        $instance->shouldIgnoreMissing(new Response());
        return $instance;
    }

    protected function injectRemovedVehiclesQueryResultData(ServiceLocatorInterface $serviceLocator, array $queryResultData): void
    {
        $removedVehiclesQueryResponse = $this->setUpQueryResponse($queryResultData);
        $this->queryHandlerMock->shouldReceive('__invoke')->withArgs(fn($query) => $query instanceof Vehicles && $query->getIncludeActive() === false)->andReturns($removedVehiclesQueryResponse)->byDefault();
    }

    /**
     * @return Parameters
     */
    protected function parametersWhichIncludeRemoved(): Parameters
    {
        return new Parameters(['includeRemoved' => '']);
    }
}
