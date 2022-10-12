<?php

namespace Olcs\Listener\RouteParam;

use Interop\Container\ContainerInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;

/**
 * Class Cases
 * @package Olcs\Listener\RouteParam
 */
class Action implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'action',
            [$this, 'onAction'],
            $priority
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onAction(RouteParam $e)
    {
        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('action')->set($e->getValue());
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : Action
    {
        return $this->__invoke($serviceLocator, Action::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Action
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Action
    {
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        return $this;
    }
}
