<?php

/**
 * Module
 *
 * @author Someone <someone@valtech.co.uk>
 */

namespace Olcs;

use Common\Exception\ResourceNotFoundException;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
use Laminas\Navigation\Service\DefaultNavigationFactory;
use Laminas\View\Model\ViewModel;
use Olcs\Listener\HeaderSearch;
use Olcs\Listener\RouteParams;

/**
 * Module
 *
 * @author Someone <someone@valtech.co.uk>
 */
class Module
{
    public static string $dateFormat = 'd/m/Y';
    public static string $dateTimeFormat = 'd/m/Y H:i';
    public static string $dateTimeSecFormat = 'd/m/Y H:i:s';

    /**
     * Event to Bootstrap the module
     *
     * @param MvcEvent $e MVC Event
     *
     * @return void
     */
    public function onBootstrap(MvcEvent $e)
    {
        $config = $e->getApplication()->getServiceManager()->get('Config');

        self::$dateFormat = $config['date_settings']['date_format'] ?? self::$dateFormat;
        self::$dateTimeFormat = $config['date_settings']['datetime_format'] ?? self::$dateTimeFormat;
        self::$dateTimeSecFormat = $config['date_settings']['datetimesec_format'] ?? self::$dateTimeSecFormat;

        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $viewHelperManager = $e->getApplication()->getServiceManager()->get('ViewHelperManager');
        $placeholder = $viewHelperManager->get('placeholder');

        $placeholder->getContainer('pageTitle')->setSeparator(' / ');
        $placeholder->getContainer('pageSubtitle')->setSeparator(' / ');

        $headTitleHelper = $viewHelperManager->get('headTitle');
        $headTitleHelper->setSeparator(' - ');
        //$headTitleHelper->setDefaultAttachOrder(AbstractContainer::PREPEND);
        $headTitleHelper->append('Olcs');

        $eventManager->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            function (MvcEvent $e) {
                $exception = $e->getParam('exception');
                // If something throws an uncaught ResourceNotFoundException in
                // an HTTP context, return a 404
                if (
                    $exception instanceof ResourceNotFoundException
                    && $e->getResponse() instanceof \Laminas\Http\Response
                ) {
                    $model = new ViewModel(
                        [
                            'message'   => $exception->getMessage(),
                            'reason'    => 'error-resource-not-found',
                            'exception' => $exception,
                        ]
                    );
                    $model->setTemplate('error/404');
                    $e->getViewModel()->addChild($model);
                    $e->getResponse()->setStatusCode(404);
                    $e->stopPropagation();
                    return $model;
                }
            }
        );

        $headerSearch = $e->getApplication()->getServiceManager()->get(HeaderSearch::class);
        $headerSearch->attach($eventManager);

        $routeParams = $e->getApplication()->getServiceManager()->get(RouteParams::class);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, [$routeParams, 'onDispatch'], 10);

        $eventManager->attach(
            MvcEvent::EVENT_ROUTE,
            function (MvcEvent $e) {
                $routeMatch = $e->getRouteMatch();

                $controllerManager = $e->getApplication()->getServiceManager()->get('ControllerManager');
                $controllerClass = $controllerManager->get($routeMatch->getParam('controller'));
                $controllerFQCN = $controllerClass::class;

                $container = $e->getApplication()->getServiceManager();
                $config = $container->get('Config');

                /** @var RouteParams $routeParamsListener */
                $routeParamsListener = $container->get(RouteParams::class);
                foreach ($config['route_param_listeners'] as $interface => $listeners) {
                    if (is_a($controllerFQCN, $interface, true)) {
                        foreach ($listeners as $listener) {
                            $listenerInstance = $container->get($listener);
                            $listenerInstance->attach($routeParamsListener->getEventManager());
                        }
                    }
                }
            }
        );
    }

    /**
     * get module config
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
