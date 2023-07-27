<?php

namespace OlcsTest\Controller\Lva\Licence;

use Common\Controller\Lva\Adapters\LicencePeopleAdapter;
use Common\Controller\Plugin\HandleQuery;
use Common\Exception\ResourceNotFoundException;
use Common\Form\Form;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FormHelperService;
use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\Licence\People;
use Hamcrest\Core\IsAnything;
use Hamcrest\Core\IsInstanceOf;
use Laminas\Form\Fieldset;
use Laminas\Http\Request;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\I18n\Translator;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\Http\TreeRouteStack;
use Laminas\Mvc\Router\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Model\ViewModel;
use Mockery\MockInterface;
use Olcs\Controller\Lva\Licence\PeopleController;
use Olcs\Controller\Lva\Licence\PeopleControllerFactory;

/**
 * @see PeopleController
 */
class PeopleControllerTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @test
     */
    public function editAction_IsCallable()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator, new Request());

        // Assert
        $this->assertTrue(method_exists($sut, 'editAction') && is_callable([$sut, 'editAction']));
    }

    /**
     * @test
     * @depends editAction_IsCallable
     */
    public function editAction_ReturnsViewModel()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $routeMatch = new RouteMatch(['child_id' => $personId = 7]);
        $sut = $this->setUpSut($serviceLocator, new Request(), $routeMatch);
        $this->injectPersonDetails($serviceLocator, ['person' => ['id' => $personId]]);

        // Execute
        $result = $sut->editAction();

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     * @depends editAction_ReturnsViewModel
     */
    public function editAction_DisplaysCorrectTitle()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $routeMatch = new RouteMatch(['child_id' => $personId = 7]);
        $sut = $this->setUpSut($serviceLocator, new Request(), $routeMatch);
        $this->injectPersonDetails($serviceLocator, ['person' => ['id' => $personId]]);

        // Execute
        $result = $sut->editAction();
        assert($result instanceof ViewModel, 'Expected instance of ViewModel');
        $section = array_values($result->getChildren())[0];

        // Assert
        $this->assertEquals('lva.section.title.edit_people', $section->getVariable('title'));
    }

    /**
     * @test
     * @depends editAction_IsCallable
     */
    public function editAction_TranslatesTitle()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $routeMatch = new RouteMatch(['child_id' => $personId = 7]);
        $sut = $this->setUpSut($serviceLocator, new Request(), $routeMatch);
        $this->injectPersonDetails($serviceLocator, ['person' => ['id' => $personId]]);

        // Define Expectations
        $this->resolveMockService($serviceLocator, TranslatorInterface::class)
            ->shouldReceive('translate')
            ->with('lva.section.title.edit_people')
            ->once();

        // Execute
        $sut->editAction();
    }

    /**
     * @test
     * @depends editAction_ReturnsViewModel
     */
    public function editAction_ReplacesPersonsNameInTitle()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $routeMatch = new RouteMatch(['child_id' => $personId = 7]);
        $sut = $this->setUpSut($serviceLocator, new Request(), $routeMatch);
        $this->injectPersonDetails($serviceLocator, ['person' => [
            'id' => $personId,
            'forename' => 'foo',
            'familyName' => 'baz',
            'title' => 'Mr',
        ]]);
        $this->resolveMockService($serviceLocator, TranslatorInterface::class)
            ->shouldReceive('translate')
            ->with('lva.section.title.edit_people')
            ->andReturn('%s');

        // Execute
        $result = $sut->editAction();
        assert($result instanceof ViewModel, 'Expected instance of ViewModel');
        $section = array_values($result->getChildren())[0];

        // Assert
        $this->assertEquals('Mr foo baz', $section->getVariable('title'));
    }

    /**
     * @test
     * @depends editAction_ReplacesPersonsNameInTitle
     */
    public function editAction_EscapesPersonsNameInTitle()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $routeMatch = new RouteMatch(['child_id' => $personId = 7]);
        $sut = $this->setUpSut($serviceLocator, new Request(), $routeMatch);
        $this->injectPersonDetails($serviceLocator, ['person' => [
            'id' => $personId,
            'forename' => $expectedFullName = '<i>FOO</i>',
        ]]);

        // Execute
        $result = $sut->editAction();
        assert($result instanceof ViewModel, 'Expected instance of ViewModel');
        $section = array_values($result->getChildren())[0];

        // Assert
        $this->assertStringNotContainsString($expectedFullName, $section->getVariable('title'));
    }

    /**
     * @test
     * @depends editAction_IsCallable
     */
    public function editAction_ThrowsResourceNotFoundException_IfPersonNotFound()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $routeMatch = new RouteMatch(['child_id' => 7]);
        $sut = $this->setUpSut($serviceLocator, new Request(), $routeMatch);

        // Define Expectations
        $this->expectException(ResourceNotFoundException::class);

        // Execute
        $sut->editAction();
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     */
    protected function setUpDefaultServices(ServiceLocatorInterface $serviceLocator): array
    {
        return [
            'Helper\Form' => $this->setUpFormHelperService($serviceLocator),
            'handleQuery' => $queryHandler = $this->setUpQueryHandler(),
            HandleQuery::class => $queryHandler,
            TranslatorInterface::class => $translator = $this->setUpTranslator(),
            'translator' => $translator,
        ];
    }

    /**
     * @return MockInterface|Translator
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
     * @return array
     */
    protected function setUpDefaultPeopleData(): array
    {
        return [
            'organisation' => ['type' => ['id' => 'foo'],],
            'people' => [],
        ];
    }

    /**
     * @return MockInterface
     */
    protected function setUpQueryHandler(): MockInterface
    {
        $instance = $this->setUpMockService(HandleQuery::class);
        $instance->shouldReceive('__invoke')->with(IsInstanceOf::anInstanceOf(People::class))->andReturnUsing(function () {
            $response = new Response(new \Laminas\Http\Response());
            $response->setResult($this->setUpDefaultPeopleData());
            return $response;
        })->byDefault();
        $instance->shouldReceive('__invoke')->with(IsInstanceOf::anInstanceOf(Licence::class))->andReturnUsing(function () {
            $response = new Response(new \Laminas\Http\Response());
            $response->setResult([
                'inForceDate' => null,
                'expiryDate' => null,
                'status' => null,
                'licNo' => null,
            ]);
            return $response;
        })->byDefault();
        return $instance;
    }


    /**
     * @return MockInterface|FormHelperService
     */
    protected function setUpFormHelperService(): MockInterface
    {
        $any = IsAnything::anything();
        $instance = $this->setUpMockService(FormHelperService::class);
        $instance->shouldReceive('createFormWithRequest')->with('Lva\Person', $any)->andReturnUsing(function () {
            $elem = new Fieldset('form-actions');
            $form = new Form();
            $form->add($elem);
            return $form;
        })->byDefault();
        return $instance;
    }


    /**
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return MvcEvent
     */
    protected function setUpMvcEvent(Request $request, RouteMatch $routeMatch): MvcEvent
    {
        $event = new MvcEvent();
        $event->setRequest($request);
        $event->setRouteMatch($routeMatch);
        $router = $this->setUpMockService(TreeRouteStack::class);
        $event->setRouter($router);
        return $event;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return LicencePeopleAdapter
     */
    protected function setUpAdaptor(ServiceLocatorInterface $serviceLocator): LicencePeopleAdapter
    {
        return new LicencePeopleAdapter($serviceLocator);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return PluginManager
     */
    protected function setUpPluginManager(ServiceLocatorInterface $serviceLocator): PluginManager
    {
        $pluginManager = new PluginManager();
        $pluginManager->setService('handleQuery', $serviceLocator->get('handleQuery'));
        $pluginManager->setServiceLocator($serviceLocator);
        return $pluginManager;
    }



    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param Request $request
     * @param RouteMatch|null $routeMatch
     * @return PeopleController
     */
    protected function setUpSut(ServiceLocatorInterface $serviceLocator, Request $request, RouteMatch $routeMatch = null): PeopleController
    {
        if (null === $routeMatch) {
            $routeMatch = new RouteMatch([]);
        }
        $factory = new PeopleControllerFactory();
        $instance = $factory->createService($serviceLocator);
        $instance->setServiceLocator($serviceLocator);
        $instance->setEvent($this->setUpMvcEvent($request, $routeMatch));
        $instance->setAdapter($this->setUpAdaptor($serviceLocator));
        $instance->setPluginManager($this->setUpPluginManager($serviceLocator));

        // Dispatch a request so that the request gets set on the controller.
        $instance->dispatch($request);

        return $instance;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array $personData
     */
    protected function injectPersonDetails(ServiceLocatorInterface $serviceLocator, array $personData)
    {
        $queryHandler = $this->resolveMockService($serviceLocator, 'handleQuery');
        $queryHandler->shouldReceive('__invoke')->with(IsInstanceOf::anInstanceOf(People::class))->andReturnUsing(function () use ($personData) {
            $response = new Response(new \Laminas\Http\Response());
            $response->setResult(array_merge($this->setUpDefaultPeopleData(), [
                'people' => [array_merge(['position' => 'bar'], $personData),],
            ]));
            return $response;
        });
    }
}
