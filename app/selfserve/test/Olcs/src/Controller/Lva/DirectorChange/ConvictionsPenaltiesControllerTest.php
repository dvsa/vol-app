<?php

namespace OlcsTest\Controller\Lva\DirectorChange;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Form\Model\Form\Lva\ConvictionsPenalties;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Dvsa\Olcs\Transfer\Command\Variation\GrantDirectorChange;
use Dvsa\Olcs\Transfer\Query\Application\Application;
use Dvsa\Olcs\Transfer\Query\Application\PreviousConvictions;
use Hamcrest\Core\IsEqual;
use Hamcrest\Core\IsInstanceOf;
use Hamcrest\Text\StringContains;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Http\Request;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\I18n\Translator;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\Mvc\Router\Http\TreeRouteStack;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Parameters;
use Mockery\MockInterface;
use Olcs\Controller\Lva\DirectorChange\ConvictionsPenaltiesController;
use Olcs\Controller\Lva\DirectorChange\ConvictionsPenaltiesControllerFactory;
use ZfcRbac\Service\AuthorizationService;

/**
 * @see ConvictionsPenaltiesController
 */
class ConvictionsPenaltiesControllerTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @test
     */
    public function indexAction_IsCallable()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $request = $this->setUpRequest();
        $sut = $this->setUpSut($serviceLocator, $request);

        // Assert
        $this->assertTrue(method_exists($sut, 'indexAction') && is_callable([$sut, 'indexAction']));
    }

    /**
     * @test
     * @depends indexAction_IsCallable
     */
    public function indexAction_FlashesSuccessMessage_WhenSingleDirectorIsCreated()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $request = $this->setUpRequest();
        $sut = $this->setUpSut($serviceLocator, $request);
        $grantDirectorHttpResponse = new \Laminas\Http\Response();
        $grantDirectorHttpResponse->setContent('{"id": {"createdPerson": 7}}');
        $grantDirectorResponse = new Response($grantDirectorHttpResponse);
        $commandHandler = $this->resolveMockService($serviceLocator, 'handleCommand');
        $grantDirectorMatcher = IsInstanceOf::anInstanceOf(GrantDirectorChange::class);
        $commandHandler->shouldReceive('__invoke')->with($grantDirectorMatcher)->andReturn($grantDirectorResponse);

        // Define Expectations
        $this->resolveMockService($serviceLocator, 'FlashMessenger')
            ->shouldReceive('addSuccessMessage')
            ->with(StringContains::containsString('singular'))
            ->once();

        // Execute
        $sut->indexAction();
    }

    /**
     * @test
     * @depends indexAction_IsCallable
     */
    public function indexAction_FlashesSuccessMessage_WhenMultipleDirectorsAreCreated()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $request = $this->setUpRequest();
        $sut = $this->setUpSut($serviceLocator, $request);
        $grantDirectorHttpResponse = new \Laminas\Http\Response();
        $grantDirectorHttpResponse->setContent('{"id": {"createdPerson": [7, 5]}}');
        $grantDirectorResponse = new Response($grantDirectorHttpResponse);
        $commandHandler = $this->resolveMockService($serviceLocator, 'handleCommand');
        $grantDirectorMatcher = IsInstanceOf::anInstanceOf(GrantDirectorChange::class);
        $commandHandler->shouldReceive('__invoke')->with($grantDirectorMatcher)->andReturn($grantDirectorResponse);

        // Define Expectations
        $this->resolveMockService($serviceLocator, 'FlashMessenger')
            ->shouldReceive('addSuccessMessage')
            ->with(StringContains::containsString('plural'))
            ->once();

        // Execute
        $sut->indexAction();
    }

    /**
     * @test
     * @depends indexAction_FlashesSuccessMessage_WhenSingleDirectorIsCreated
     */
    public function indexAction_FlashesSuccessMessage_ReplacesDirectorsCreatedCount()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $request = $this->setUpRequest();
        $sut = $this->setUpSut($serviceLocator, $request);
        $grantDirectorHttpResponse = new \Laminas\Http\Response();
        $grantDirectorHttpResponse->setContent('{"id": {"createdPerson": 7}}');
        $grantDirectorResponse = new Response($grantDirectorHttpResponse);
        $commandHandler = $this->resolveMockService($serviceLocator, 'handleCommand');
        $grantDirectorMatcher = IsInstanceOf::anInstanceOf(GrantDirectorChange::class);
        $commandHandler->shouldReceive('__invoke')->with($grantDirectorMatcher)->andReturn($grantDirectorResponse);
        $this->resolveMockService($serviceLocator, TranslatorInterface::class)->shouldReceive('translate')->andReturn('%s');

        // Define Expectations
        $this->resolveMockService($serviceLocator, 'FlashMessenger')
            ->shouldReceive('addSuccessMessage')
            ->with(IsEqual::equalTo('1'))
            ->once();

        // Execute
        $sut->indexAction();
    }
    
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     */
    protected function setUpDefaultServices(ServiceLocatorInterface $serviceLocator): array
    {
        return [
            TranslatorInterface::class => $translator = $this->setUpTranslator(),
            'translator' => $translator,
            'Helper\Translation' => $this->setUpMockService(TranslationHelperService::class),
            'Helper\Url' => $this->setUpMockService(UrlHelperService::class),
            'redirect' => $this->setUpMockService(Redirect::class),
            'Table' => $this->setUpTableFactory(),
            'FlashMessenger' => $this->setUpMockService(FlashMessenger::class),
            'handleQuery' => $this->setUpQueryHandler(),
            'Helper\Form' => $this->setUpFormHelperService(),
            'FormServiceManager' => $this->setUpFormServiceManager($serviceLocator),
            'Config' => [
                'csrf' => [
                    'timeout' => 300,
                ]
            ],
            AuthorizationService::class => $this->setUpMockService(AuthorizationService::class),
            'Script' => $this->setUpMockService(ScriptFactory::class),
            'Helper\Restriction' => $this->setUpMockService(RestrictionHelperService::class),
            'Helper\String' => $this->setUpMockService(StringHelperService::class),
            'handleCommand' => $this->setUpCommandHandler(),
        ];
    }

    /**
     * @return MockInterface
     */
    protected function setUpTranslator(): MockInterface
    {
        $instance = $this->setUpMockService(Translator::class);
        $instance->shouldReceive('translate')->andReturnUsing(function ($key) {
            return $key;
        })->byDefault();
        return $instance;
    }

    /**
     * @return MockInterface
     */
    protected function setUpCommandHandler(): MockInterface
    {
        $instance = $this->setUpMockService(HandleCommand::class);
        $instance->shouldReceive('__invoke')->andReturnUsing(function ($command) {
            $httpResponse = new \Laminas\Http\Response();
            $response = new Response($httpResponse);
            switch (get_class($command)) {
                case GrantDirectorChange::class:
                    $httpResponse->setContent('{"id": {"createdPerson": []}}');
                    break;
            }
            return $response;
        })->byDefault();
        return $instance;
    }

    /**
     * @return MockInterface
     */
    protected function setUpTableFactory(): MockInterface
    {
        $instance = $this->setUpMockService(TableFactory::class);
        $instance->shouldReceive('prepareTable')->andReturnUsing(function () {
            $instance = $this->setUpMockService(TableBuilder::class);
            $instance->shouldReceive('getRows')->andReturn([])->byDefault();
            return $instance;
        })->byDefault();
        return $instance;
    }

    /**
     * @return FormHelperService
     */
    protected function setUpFormHelperService(): FormHelperService
    {
        $instance = $this->setUpMockService(FormHelperService::class);
        $instance->shouldReceive('createForm')->andReturnUsing(function () {
            $annotationBuilder = new AnnotationBuilder();
            $form = $annotationBuilder->createForm(ConvictionsPenalties::class);
            return $form;
        })->byDefault();
        return $instance;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return FormServiceManager
     */
    protected function setUpFormServiceManager(ServiceLocatorInterface $serviceLocator): FormServiceManager
    {
        $instance = new FormServiceManager(new Config([
            'invokables' => [
                'lva-variation-convictions_penalties' => \Common\FormService\Form\Lva\ConvictionsPenalties::class,
            ],
        ]));
        $instance->setServiceLocator($serviceLocator);
        return $instance;
    }

    /**
     * @return MockInterface
     */
    protected function setUpQueryHandler(): MockInterface
    {
        $instance = $this->setUpMockService(HandleQuery::class);

        $instance->shouldReceive('__invoke')->andReturnUsing(function ($query) {
            $response = new Response(new \Laminas\Http\Response());
            $response->setResult([]);
            switch (get_class($query)) {
                case PreviousConvictions::class:
                    $response->setResult([
                        'version' => 1,
                        'prevConviction' => null,
                        'convictionsConfirmation' => null,
                        'previousConvictions' => [],
                    ]);
                    break;
                case Application::class:
                    $response->setResult([
                        'licence' => [
                            'id' => 3,
                            'licNo' => 'OC823Y289',
                            'organisation' => [
                                'type' => [
                                    'id' => 'foo',
                                ],
                            ],
                        ],
                        'isVariation' => false,
                        'sections' => [],
                        'applicationCompletion' => [],
                        'status' => [
                            'id' => RefData::APPLICATION_STATUS_VALID,
                        ],
                    ]);
                    break;
            }
            return $response;
        })->byDefault();

        return $instance;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return PluginManager
     */
    protected function setUpPluginManager(ServiceLocatorInterface $serviceLocator): PluginManager
    {
        $pluginManager = new PluginManager();
        $pluginManager->setService('handleQuery', $serviceLocator->get('handleQuery'));
        $pluginManager->setService('handleCommand', $serviceLocator->get('handleCommand'));
        $pluginManager->setService('redirect', $serviceLocator->get('redirect'));
        $pluginManager->setServiceLocator($serviceLocator);
        return $pluginManager;
    }

    /**
     * @param array|null $data
     * @return Request
     */
    protected function setUpRequest(array $data = null): Request
    {
        if (null === $data) {
            $data = [
                'data' => [
                    'table' => null,
                    'question' => 'Y',
                ],
                'convictionsConfirmation' => 'N',
            ];
        }
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(new Parameters($data));
        return $request;
    }

    /**
     * @param Request $request
     * @return MvcEvent
     */
    protected function setUpMvcEvent(Request $request): MvcEvent
    {
        $event = new MvcEvent();
        $event->setRequest($request);

        $routeMatch = new RouteMatch([]);
        $event->setRouteMatch($routeMatch);

        $router = $this->setUpMockService(TreeRouteStack::class);
        $event->setRouter($router);

        return $event;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param Request $request
     * @return ConvictionsPenaltiesController
     */
    protected function setUpSut(ServiceLocatorInterface $serviceLocator, Request $request): ConvictionsPenaltiesController
    {
        $factory = new ConvictionsPenaltiesControllerFactory();
        $instance = $factory->createService($serviceLocator);
        $instance->setServiceLocator($serviceLocator);
        $instance->setEvent($this->setUpMvcEvent($request));

        $instance->setPluginManager($this->setUpPluginManager($serviceLocator));

        // Dispatch a request so that the request gets set on the controller.
        $instance->dispatch($request);

        return $instance;
    }
}
