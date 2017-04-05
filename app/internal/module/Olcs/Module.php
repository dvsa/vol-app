<?php

/**
 * Module
 *
 * @author Someone <someone@valtech.co.uk>
 */
namespace Olcs;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\Placeholder\Container\AbstractContainer;
use Zend\View\Model\ViewModel;
use Common\Exception\ResourceNotFoundException;

/**
 * Module
 *
 * @author Someone <someone@valtech.co.uk>
 */
class Module
{
    /**
     * Event to Bootstrap the module
     *
     * @param MvcEvent $e MVC Event
     *
     * @return void
     */
    public function onBootstrap(MvcEvent $e)
    {

        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');
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
                if ($exception instanceof ResourceNotFoundException
                    && $e->getResponse() instanceof \Zend\Http\Response
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
    }

    /**
     * get module config
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * get Autoloader config for this module
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/',
                ),
            ),
        );
    }
}
