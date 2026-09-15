<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

use Common\Rbac\Service\Permission;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\FormatterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * A container carrying the services the real formatter factories ask for, so that real formatters
 * execute. Using mocked formatters would test the harness, not the formatters.
 *
 * Shared by both escaping harnesses. TableEscapingHarness drives formatters indirectly, through a
 * rendered table; FormatterEscapingHarness calls them directly. They need the same services, and a
 * second copy of these stubs would drift from this one the first time a formatter gained a
 * dependency.
 *
 * These are fakes, not expectations, and withAnyArgs() is deliberate rather than lazy. One
 * container serves every formatter in the application: fromRoute() is called with a different route
 * and parameters by each of the ~90 that use it, translate() with whatever key the formatter holds.
 * A specific with() here could only be satisfied by one caller and would fail the other eighty-nine,
 * so it would have to be widened again the moment a formatter changed — the assertion would be
 * fiction. What each formatter is called with is asserted in that formatter's own unit test, where
 * the arguments are actually known. withNoArgs() is used wherever the method genuinely takes none,
 * because there the declaration is real.
 */
final class HarnessContainer
{
    public static function create(): ServiceManager
    {
        $urlHelper = m::mock(UrlHelperService::class);
        $urlHelper->shouldReceive('fromRoute')->withAnyArgs()->andReturn('/stub-url');
        $urlHelper->shouldIgnoreMissing('/stub-url');

        // TranslatorDelegator implements the I18n TranslatorInterface, so a single mock satisfies
        // both TableBuilder (which wants the interface) and the ~19 formatters whose constructors
        // name the delegator concretely. The factories fetch it as 'translator'.
        $translator = m::mock(\Dvsa\Olcs\Utils\Translation\TranslatorDelegator::class);
        $translator->shouldReceive('translate')->withAnyArgs()->andReturnUsing(
            static fn($message) => is_string($message) ? $message : ''
        );
        $translator->shouldIgnoreMissing('');

        $permission = m::mock(Permission::class);
        $permission->shouldReceive('isGranted')->withAnyArgs()->andReturn(true);
        $permission->shouldReceive('isInternalReadOnly')->withNoArgs()->andReturn(false);
        $permission->shouldIgnoreMissing(false);

        // A real class, not a closure or a mock: helpers are called both as $helper(...) and as
        // $helper->render(...), and neither of those alternatives satisfies both.
        $viewHelperManager = m::mock(\Laminas\View\HelperPluginManager::class);
        $viewHelperManager->shouldReceive('get')->withAnyArgs()->andReturn(new HarnessViewHelper());

        $router = m::mock(\Laminas\Router\RouteStackInterface::class);
        $router->shouldIgnoreMissing('/stub-url');

        $request = new \Laminas\Http\Request();

        $authorization = m::mock(AuthorizationService::class);
        $authorization->shouldReceive('isGranted')->withAnyArgs()->andReturn(true);
        $authorization->shouldIgnoreMissing(true);

        // Formatter constructors are typed, so these have to be mocks of the real classes — a
        // generic mock is rejected by the parameter type and the formatter never runs.
        $dataHelper = m::mock(\Common\Service\Helper\DataHelperService::class);
        $dataHelper->shouldReceive('fetchNestedData')->withAnyArgs()->andReturnUsing(
            static fn($data) => $data
        );
        $dataHelper->shouldIgnoreMissing('');

        // Real, not a mock, and the distinction matters more than it looks. getStackValue() is a
        // pure walk down a nested array with no dependencies of its own, and it is how StackValue,
        // NumberStackValue and FeeTransactionDate read the value they format. Mocked with
        // shouldIgnoreMissing(''), it handed all three an empty string instead of the row value —
        // so they rendered nothing, could not leak by construction, and counted as exercised while
        // asserting nothing at all. A fake is only safe where the real thing needs the world.
        $stackHelper = new \Common\Service\Helper\StackHelperService();

        $dateHelper = m::mock(\Common\Service\Helper\DateHelperService::class);
        $dateHelper->shouldIgnoreMissing('');

        // match() has to return a RouteMatch, not the catch-all string. Several formatters branch
        // on $this->router->match($request)->getMatchedRouteName(), and answering with a string
        // makes them die on "getMatchedRouteName() on string" before they format anything.
        // Http\RouteMatch, not the base class: Formatter\InternalConversationLink type-hints the
        // HTTP subclass, and a mock of the parent is rejected by the constructor.
        $routeMatch = m::mock(\Laminas\Router\Http\RouteMatch::class);
        $routeMatch->shouldReceive('getMatchedRouteName')->withNoArgs()->andReturn('stub-route');
        $routeMatch->shouldReceive('getParams')->withNoArgs()->andReturn([]);
        // "type" decides which route a conversation link builds and is validated against a known
        // list, so the catch-all string makes InternalConversationLink throw before it formats.
        // Declared first because Mockery matches the most recently declared expectation that fits.
        $routeMatch->shouldReceive('getParam')->with('type')->andReturn('licence');
        $routeMatch->shouldReceive('getParam')->withAnyArgs()->andReturn('stub-param');
        $routeMatch->shouldIgnoreMissing('stub-route');

        $routeStack = m::mock(\Laminas\Router\Http\TreeRouteStack::class);
        $routeStack->shouldReceive('match')->withAnyArgs()->andReturn($routeMatch);
        $routeStack->shouldIgnoreMissing('/stub-url');

        $router->shouldReceive('match')->withAnyArgs()->andReturn($routeMatch);

        // Pass-through like the translator above, so a row value routed through translateReplace()
        // still carries the marker into the output rather than being swallowed.
        $translationHelper = m::mock(\Common\Service\Helper\TranslationHelperService::class);
        $translationHelper->shouldReceive('translate')->withAnyArgs()->andReturnUsing(
            static fn($message) => is_string($message) ? $message : ''
        );
        $translationHelper->shouldReceive('translateReplace')->withAnyArgs()->andReturnUsing(
            static fn($key, $args) => implode(' ', array_map(
                static fn(mixed $arg): string => is_scalar($arg) || $arg instanceof \Stringable ? (string)$arg : '',
                is_array($args) ? $args : []
            ))
        );
        $translationHelper->shouldIgnoreMissing('');

        $services = [
            'Helper\Url' => $urlHelper,
            UrlHelperService::class => $urlHelper,
            'translator' => $translator,
            'Translator' => $translator,
            \Laminas\I18n\Translator\TranslatorInterface::class => $translator,
            \Dvsa\Olcs\Utils\Translation\TranslatorDelegator::class => $translator,
            Permission::class => $permission,
            'ViewHelperManager' => $viewHelperManager,
            \Laminas\View\HelperPluginManager::class => $viewHelperManager,
            'Router' => $routeStack,
            'router' => $routeStack,
            \Laminas\Router\Http\TreeRouteStack::class => $routeStack,
            \Laminas\Router\RouteStackInterface::class => $router,
            'Request' => $request,
            'request' => $request,
            \Laminas\Http\Request::class => $request,
            AuthorizationService::class => $authorization,
            'Helper\Stack' => $stackHelper,
            \Common\Service\Helper\StackHelperService::class => $stackHelper,
            'Helper\Data' => $dataHelper,
            \Common\Service\Helper\DataHelperService::class => $dataHelper,
            'Helper\Date' => $dateHelper,
            \Common\Service\Helper\DateHelperService::class => $dateHelper,
            'Helper\Translation' => $translationHelper,
            \Common\Service\Helper\TranslationHelperService::class => $translationHelper,
        ];

        $container = new ServiceManager(['services' => $services]);

        $plugins = new FormatterPluginManager($container, self::formatterConfig());
        $container->setService(FormatterPluginManager::class, $plugins);

        // Three formatter factories reach for services the plugin manager does not provide,
        // because they resolve out of the application container rather than out of the plugin
        // manager they themselves live in. Registered afterwards, since two of them are formatters
        // and need the plugin manager to exist first.
        //
        // 'TableBuilder' is deliberately not among them: TableEscapingHarness registers the real one
        // it builds, and ServiceManager refuses to replace a service that already has an instance.
        // FormatterEscapingHarness, which renders no tables, supplies its own mock instead.

        // Formatter\TmApplicationManagerType walks application->getMvcEvent()->getRouteMatch(),
        // so the event has to be an object rather than the catch-all string.
        $mvcEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $mvcEvent->shouldReceive('getRouteMatch')->withNoArgs()->andReturn($routeMatch);
        $mvcEvent->shouldIgnoreMissing('');

        $application = m::mock(\Laminas\Mvc\Application::class);
        $application->shouldReceive('getMvcEvent')->withNoArgs()->andReturn($mvcEvent);
        $application->shouldIgnoreMissing('');
        $container->setService('Application', $application);

        foreach (
            [
            \Common\Service\Table\Formatter\Date::class,
            \Common\Service\Table\Formatter\StackValue::class,
            ] as $formatter
        ) {
            $container->setService($formatter, $plugins->get($formatter));
        }

        return $container;
    }

    /**
     * The application's real formatter registrations.
     *
     * @return array<string, mixed>
     */
    public static function formatterConfig(): array
    {
        return require __DIR__ . '/../../../../../../../Common/config/formatter-plugins.config.php';
    }
}
