<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\Service\Data\Licence as LicenceService;
use Zend\Mvc\Router\RouteStackInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\Service\Entity\LicenceEntityService;

/**
 * Class Licence
 * @package Olcs\Listener\RouteParam
 */
class Licence implements ListenerAggregateInterface, FactoryInterface, ServiceLocatorAwareInterface
{
    use ListenerAggregateTrait,
        ViewHelperManagerAwareTrait,
        ServiceLocatorAwareTrait;

    /**
     * @var LicenceService
     */
    protected $licenceService;

    /**
     * @var RouteStackInterface
     */
    protected $router;

    /**
     * @param \Common\Service\Data\Licence $licenceService
     * @return $this
     */
    public function setLicenceService($licenceService)
    {
        $this->licenceService = $licenceService;
        return $this;
    }

    /**
     * @return \Common\Service\Data\Licence
     */
    public function getLicenceService()
    {
        return $this->licenceService;
    }

    /**
     * @param \Zend\Mvc\Router\RouteStackInterface $router
     * @return $this
     */
    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return \Zend\Mvc\Router\RouteStackInterface
     */
    public function getRouter()
    {
        return $this->router;
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
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'licence', array($this, 'onLicence'), 1);
    }

    /**
     * @param RouteParam $e
     */
    public function onLicence(RouteParam $e)
    {
        $this->getLicenceService()->setId($e->getValue()); //set default licence id for use in forms
        $licence = $this->getLicenceService()->fetchLicenceData($e->getValue());

        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $placeholder->getContainer('licence')->set($licence);

        // If the licence type is special restricted we can't create a variation
        if ($licence['licenceType']['id'] == LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            $sidebarNav = $this->getServiceLocator()->get('right-sidebar');
            $sidebarNav->findById('licence-quick-actions-create-variation')->setVisible(0);
        }

        $printStatuses = [
            LicenceEntityService::LICENCE_STATUS_VALID,
            LicenceEntityService::LICENCE_STATUS_CURTAILED,
            LicenceEntityService::LICENCE_STATUS_SUSPENDED
        ];

        if (!in_array($licence['status']['id'], $printStatuses)) {
            $sidebarNav = $this->getServiceLocator()->get('right-sidebar');
            $sidebarNav->findById('licence-quick-actions-print-licence')->setVisible(0);
        }
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
        $this->setLicenceService($serviceLocator->get('DataServiceManager')->get('Common\Service\Data\Licence'));
        $this->setRouter($serviceLocator->get('Router'));

        return $this;
    }
}
