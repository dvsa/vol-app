<?php

namespace Olcs\Listener\RouteParam;

use Psr\Container\ContainerInterface;
use Laminas\EventManager\EventInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use \Dvsa\Olcs\Transfer\Query\Bus\BusReg as ItemDto;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;

class BusRegId implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;

    /**
     * @var \Laminas\Navigation\Navigation
     */
    protected $navigationService;

    protected $annotationBuilder;

    protected $queryService;

    public function getAnnotationBuilder()
    {
        return $this->annotationBuilder;
    }

    public function getQueryService()
    {
        return $this->queryService;
    }

    public function setAnnotationBuilder($annotationBuilder)
    {
        $this->annotationBuilder = $annotationBuilder;
    }

    public function setQueryService($queryService)
    {
        $this->queryService = $queryService;
    }

    /**
     * @return \Laminas\Navigation\Navigation
     */
    public function getNavigationService()
    {
        return $this->navigationService;
    }

    /**
     * @param \Laminas\Navigation\Navigation $navigationService
     * @return $this
     */
    public function setNavigationService($navigationService)
    {
        $this->navigationService = $navigationService;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'busRegId',
            [$this, 'onBusRegId'],
            $priority
        );
    }

    public function onBusRegId(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $busReg = $this->getBusReg($routeParam->getValue());

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('busReg')->set($busReg);

        if ($busReg['isShortNotice'] === 'N') {
            // hide short notice navigation
            $this->getNavigationService()->findOneById('licence_bus_short')->setVisible(false);
        }

        $context = $routeParam->getContext();
        if (isset($busReg['licence']['id']) && !isset($context['licence'])) {
            // trigger the licence listener
            $routeParam->getTarget()->trigger('licence', $busReg['licence']['id']);
        }
    }

    /**
     * Get the Bus Reg data
     *
     * @param id $id
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getBusReg($id)
    {
        // for performance reasons this query should be the same as used in other BusReg RouteListeners
        $query = $this->getAnnotationBuilder()->createQuery(
            ItemDto::create(['id' => $id])
        );

        $response = $this->getQueryService()->send($query);

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Bus Reg id [$id] not found");
        }

        return $response->getResult();
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BusRegId
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : BusRegId
    {
        $this->setAnnotationBuilder($container->get('TransferAnnotationBuilder'));
        $this->setQueryService($container->get('QueryService'));
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        $this->setNavigationService($container->get('navigation'));
        return $this;
    }
}
