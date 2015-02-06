<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\Navigation\PluginManager as ViewHelperManager;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;

/**
 * Class Cases
 * @package Olcs\Listener\RouteParam
 */
class BusRegAction implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;
    use ServiceLocatorAwareTrait;

    protected $busRegService;

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
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'busRegId', [$this, 'onBusRegAction'], 1);
    }

    /**
     * @param RouteParam $e
     */
    public function onBusRegAction(RouteParam $e)
    {
        $newVariationCancellation = [
            'breg_s_new',
            'breg_s_var',
            'breg_s_cancellation'
        ];

        $newVariationCancellationButtons = [
            'bus-registration-quick-actions-request-withdrawn', //withdrawn
            'bus-registration-decisions-grant', //grant
            'bus-registration-decisions-refuse', //refuse
            'bus-registration-decisions-refuse-by-short-notice' //refuse by short notice
        ];

        $registeredButtons = [
            'bus-registration-quick-actions-create-variation', //create variation
            'bus-registration-quick-actions-create-cancellation', //create cancellation
        ];

        $service = $this->getBusRegService();
        $busReg = $service->fetchOne($e->getValue());

        $sidebarNav = $this->getSidebarNavigation();

        //if status is not new, variation or cancellation, disable corresponding nav
        if (!in_array($busReg['status']['id'], $newVariationCancellation)) {
            foreach ($newVariationCancellationButtons as $navId) {
                $sidebarNav->findById($navId)->setVisible(0);
            }
        } else {
            //status is new, variation or cancelled
            $sidebarNav->findById('bus-registration-decisions-reset-registration')->setVisible(0);

            //only show the grant button if all validation conditions are met
            if (!$service->isGrantable($busReg['id'])) {
                $sidebarNav->findById('bus-registration-decisions-grant')->setVisible(0);
            }

            //Refuse by short notice
            if ($busReg['shortNoticeRefused'] == 'Y' || $busReg['isShortNotice'] == 'N') {
                $sidebarNav->findById('bus-registration-decisions-refuse-by-short-notice')->setVisible(0);
            }
        }

        //if status is not registered, disable corresponding nav
        if ($busReg['status']['id'] != 'breg_s_registered') {
            foreach ($registeredButtons as $navId) {
                $sidebarNav->findById($navId)->setVisible(0);
            }

            $sidebarNav->findById('bus-registration-decisions-admin-cancel')->setVisible(0);
        }
    }

    /**
     * Gets the main navigation
     *
     * @return \Zend\Navigation\Navigation
     */
    public function getSidebarNavigation()
    {
        return $this->getServiceLocator()->get('right-sidebar');
    }

    /**
     * @return mixed
     */
    public function getBusRegService()
    {
        return $this->busRegService;
    }

    /**
     * @param mixed $busRegService
     */
    public function setBusRegService($busRegService)
    {
        $this->busRegService = $busRegService;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setServiceLocator($serviceLocator);

        $this->setBusRegService($serviceLocator->get('DataServiceManager')->get('Common\Service\Data\BusReg'));

        return $this;
    }
}
