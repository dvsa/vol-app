<?php

namespace Olcs\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceQuery;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Zend\View\Model\ViewModel;

/**
 * Licence Furniture
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceFurniture implements ListenerAggregateInterface, FactoryInterface, QuerySenderAwareInterface
{
    use ListenerAggregateTrait,
        ViewHelperManagerAwareTrait,
        QuerySenderAwareTrait;

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setQuerySender($serviceLocator->get('QuerySender'));

        return $this;
    }

    /**
     * Attach one or more listeners
     *
     * Implementers may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'licence',
            [$this, 'onLicenceFurniture'],
            1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onLicenceFurniture(RouteParam $e)
    {
        $id = $e->getValue();

        $response = $this->getQuerySender()->send(LicenceQuery::create(['id' => $id]));

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Licence id [$id] not found");
        }

        $data = $response->getResult();

        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $placeholder->getContainer('pageTitle')->set($data['licNo']);
        $placeholder->getContainer('pageSubtitle')->set($data['organisation']['name']);
        $placeholder->getContainer('status')->set($data['status']['id']);
        $placeholder->getContainer('horizontalNavigationId')->set('licence');

        $right = new ViewModel();
        $right->setTemplate('sections/licence/partials/right');

        $placeholder->getContainer('right')->set($right);
    }
}
