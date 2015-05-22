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
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Service\BusRegistration;

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
            BusRegistration::STATUS_NEW,
            BusRegistration::STATUS_VAR,
            BusRegistration::STATUS_CANCEL
        ];

        $service = $this->getBusRegService();
        $busReg = $service->fetchOne($e->getValue());

        $buttonsToHide = [];

        if (!$service->isLatestVariation($busReg['id'])) {
            // hide buttons which should only be available to the latest variation
            $buttonsToHide = array_merge(
                $buttonsToHide,
                [
                    'bus-registration-quick-actions-create-variation',
                    'bus-registration-quick-actions-create-cancellation',
                    'bus-registration-decisions-admin-cancel',
                    'bus-registration-decisions-reset-registration'
                ]
            );
        }

        //if status is not new, variation or cancellation, disable corresponding nav
        if (!in_array($busReg['status']['id'], $newVariationCancellation)) {
            $buttonsToHide = array_merge(
                $buttonsToHide,
                [
                    'bus-registration-quick-actions-request-withdrawn', //withdrawn
                    'bus-registration-decisions-grant', //grant
                    'bus-registration-decisions-refuse', //refuse
                    'bus-registration-decisions-refuse-by-short-notice' //refuse by short notice
                ]
            );
        } else {
            //status is new, variation or cancelled
            $buttonsToHide[] = 'bus-registration-decisions-reset-registration';

            //only show the grant button if all validation conditions are met
            $isGrantable = $this->getServiceLocator()->get('BusinessServiceManager')
                ->get('Bus\BusReg')
                ->isGrantable($busReg['id']);

            if (!$isGrantable) {
                $buttonsToHide[] = 'bus-registration-decisions-grant';
            }

            //if status is variation the grant button opens a modal instead
            if ($busReg['status']['id'] == BusRegistration::STATUS_VAR) {
                $this->getSidebarNavigation()
                    ->findById('bus-registration-decisions-grant')
                    ->setClass('action--secondary js-modal-ajax');
            }

            //Refuse by short notice
            if ($busReg['shortNoticeRefused'] == 'Y' || $busReg['isShortNotice'] == 'N') {
                $buttonsToHide[] = 'bus-registration-decisions-refuse-by-short-notice';
            }
        }

        //if status is not registered, disable corresponding nav
        if ($busReg['status']['id'] != BusRegistration::STATUS_REGISTERED) {
            $buttonsToHide = array_merge(
                $buttonsToHide,
                [
                    'bus-registration-quick-actions-create-variation', //create variation
                    'bus-registration-quick-actions-create-cancellation', //create cancellation
                    'bus-registration-decisions-admin-cancel'
                ]
            );
        }

        //if status is not registered or cancelled, disable republish button
        if (
            !in_array(
                $busReg['status']['id'],
                [
                    BusRegistration::STATUS_REGISTERED,
                    BusRegistration::STATUS_CANCELLED
                ]
            )
        ) {
            $buttonsToHide[] = 'bus-registration-quick-actions-republish';
        }

        if (empty($busReg['isTxcApp']) || $busReg['isTxcApp'] != 'Y') {
            // non Ebsr - hide new route map button
            $buttonsToHide[] = 'bus-registration-quick-actions-request-new-route-map';
        }

        $this->hideSidebarNavigationButtons(array_unique($buttonsToHide));
    }

    /**
     * Hide sidebar navigation buttons
     *
     * @param array $buttons
     */
    private function hideSidebarNavigationButtons($buttons)
    {
        $sidebarNav = $this->getSidebarNavigation();
        foreach ($buttons as $navId) {
            $sidebarNav->findById($navId)->setVisible(0);
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
