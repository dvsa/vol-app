<?php

namespace Admin\Listener\RouteParam;

use Common\RefData;
use Common\Service\Cqrs\Command\CommandSenderAwareInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareTrait;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\ById as ItemDto;
use Laminas\View\Helper\Placeholder;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;

/**
 * IRHP Permit Admin Furniture
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class IrhpPermitAdminFurniture implements
    ListenerAggregateInterface,
    FactoryInterface,
    QuerySenderAwareInterface,
    CommandSenderAwareInterface
{
    use ListenerAggregateTrait,
        ViewHelperManagerAwareTrait,
        QuerySenderAwareTrait,
        CommandSenderAwareTrait;

    /**
     * @var Navigation
     */
    protected $navigationService;

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setQuerySender($serviceLocator->get('QuerySender'));
        $this->setCommandSender($serviceLocator->get('CommandSender'));
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setNavigationService($serviceLocator->get('Navigation'));

        return $this;
    }

    /**
     * @return Navigation
     */
    public function getNavigationService()
    {
        return $this->navigationService;
    }

    /**
     * @param Navigation $navigationService
     * @return $this
     */
    public function setNavigationService(Navigation $navigationService)
    {
        $this->navigationService = $navigationService;
        return $this;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'stockId',
            [$this, 'onIrhpPermitAdminFurniture'],
            1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onIrhpPermitAdminFurniture(RouteParam $e)
    {
        $id = $e->getValue();

        $permitStock = $this->getIrhpPermitStock($id);

        /** @var Placeholder $placeholder */
        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('pageTitle')->set('Permits');
        $placeholder->getContainer('pageSubtitle')->set($this->setSubtitle($permitStock));

        $nonScoringTypes = [
            RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
            RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
            RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
            RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
        ];

        if (in_array($permitStock['irhpPermitType']['id'], $nonScoringTypes)) {
            $this->getNavigationService()->findOneBy('id', 'admin-dashboard/admin-permits/jurisdiction')
                ->setVisible(false);
            $this->getNavigationService()->findOneBy('id', 'admin-dashboard/admin-permits/sectors')
                ->setVisible(false);
            $this->getNavigationService()->findOneBy('id', 'admin-dashboard/admin-permits/scoring')
                ->setVisible(false);
        }
    }

    /**
     * Get the Irhp Permit data
     *
     * @param int $id
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getIrhpPermitStock($id)
    {
        $response = $this->getQuerySender()->send(
            ItemDto::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Irhp Permit id [$id] not found");
        }

        return $response->getResult();
    }

    private function setSubtitle($permitStock)
    {
        //the format for international removals is different to other permit types
        if ($permitStock['irhpPermitType']['isEcmtRemoval']) {
            return sprintf(
                "Type: %s Stock: %s Quota: %s",
                $permitStock['irhpPermitType']['name']['description'],
                $permitStock['id'],
                $permitStock['initialStock']
            );
        }

        $validFrom = date('d/m/Y', strtotime($permitStock['validFrom']));
        $validTo = date('d/m/Y', strtotime($permitStock['validTo']));
        $initialStock = $permitStock['initialStock'];
        $name = $permitStock['irhpPermitType']['name']['description'];
        if ($permitStock['irhpPermitType']['id'] === RefData::IRHP_BILATERAL_PERMIT_TYPE_ID && !empty($permitStock['country']['countryDesc'])) {
            $name .= ' ('.$permitStock['country']['countryDesc'].') ';
        }

        return sprintf(
            "Type: %s Validity: %s to %s Quota: %s",
            $name,
            $validFrom,
            $validTo,
            $initialStock
        );
    }
}
