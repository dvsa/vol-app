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
 */
final class HarnessContainer
{
    public static function create(): ServiceManager
    {
        $urlHelper = m::mock(UrlHelperService::class);
        $urlHelper->shouldReceive('fromRoute')->andReturn('/stub-url');
        $urlHelper->shouldIgnoreMissing('/stub-url');

        // TranslatorDelegator implements the I18n TranslatorInterface, so a single mock satisfies
        // both TableBuilder (which wants the interface) and the ~19 formatters whose constructors
        // name the delegator concretely. The factories fetch it as 'translator'.
        $translator = m::mock(\Dvsa\Olcs\Utils\Translation\TranslatorDelegator::class);
        $translator->shouldReceive('translate')->andReturnUsing(
            static fn($message) => is_string($message) ? $message : ''
        );
        $translator->shouldIgnoreMissing('');

        $permission = m::mock(Permission::class);
        $permission->shouldReceive('isGranted')->andReturn(true);
        $permission->shouldReceive('isInternalReadOnly')->andReturn(false);
        $permission->shouldIgnoreMissing(false);

        $viewHelperManager = m::mock(\Laminas\View\HelperPluginManager::class);
        $viewHelperManager->shouldReceive('get')->andReturn(static fn(...$args) => '');

        $router = m::mock(\Laminas\Router\RouteStackInterface::class);
        $router->shouldIgnoreMissing('/stub-url');

        $request = new \Laminas\Http\Request();

        $authorization = m::mock(AuthorizationService::class);
        $authorization->shouldReceive('isGranted')->andReturn(true);
        $authorization->shouldIgnoreMissing(true);

        // Formatter constructors are typed, so these have to be mocks of the real classes — a
        // generic mock is rejected by the parameter type and the formatter never runs.
        $dataHelper = m::mock(\Common\Service\Helper\DataHelperService::class);
        $dataHelper->shouldReceive('fetchNestedData')->andReturnUsing(
            static fn($data) => $data
        );
        $dataHelper->shouldIgnoreMissing('');

        $stackHelper = m::mock(\Common\Service\Helper\StackHelperService::class);
        $stackHelper->shouldIgnoreMissing('');

        $dateHelper = m::mock(\Common\Service\Helper\DateHelperService::class);
        $dateHelper->shouldIgnoreMissing('');

        $routeStack = m::mock(\Laminas\Router\Http\TreeRouteStack::class);
        $routeStack->shouldIgnoreMissing('/stub-url');

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
        ];

        $container = new ServiceManager(['services' => $services]);

        $container->setService(
            FormatterPluginManager::class,
            new FormatterPluginManager($container, self::formatterConfig()),
        );

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
