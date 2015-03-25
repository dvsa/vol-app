<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Service\Data\ApplicationAwareTrait;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Exception\ResourceNotFoundException;

/**
 * Class Cases
 * @package Olcs\Listener\RouteParam
 */
class Application implements ListenerAggregateInterface, FactoryInterface, ServiceLocatorAwareInterface
{
    use ListenerAggregateTrait;
    use ApplicationAwareTrait;
    use ViewHelperManagerAwareTrait;
    use ServiceLocatorAwareTrait;

    /**
     * @var NavigationService
     */
    protected $navigationService;

    /**
     * @return \Zend\Navigation\Navigation
     */
    public function getNavigationService()
    {
        return $this->navigationService;
    }

    /**
     * @param \Zend\Navigation\Navigation $navigationService
     */
    public function setNavigationService($navigationService)
    {
        $this->navigationService = $navigationService;
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
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'application', [$this, 'onApplication'], 1);
    }

    /**
     * @param RouteParam $e
     */
    public function onApplication(RouteParam $e)
    {
        $id = $e->getValue();

        $this->getApplicationService()->setId($id);
        $application = $this->getApplicationService()->fetchData($id);

        if (false === $application) {
            throw new ResourceNotFoundException("Application id [$id] not found");
        }

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('application')->set($application);

        $sidebarNav = $this->getServiceLocator()->get('right-sidebar');

        $status = $this->getServiceLocator()->get('Entity\Application')->getStatus($id);

        $showGrantButton = $this->shouldShowGrantButton($status);
        $showWithdrawButton = $this->shouldShowWithdrawButton($status);
        $showRefuseButton = $this->shouldShowRefuseButton($status);

        if ($showGrantButton) {
            $showUndoGrantButton = false;
        } else {
            $showUndoGrantButton = $this->shouldShowUndoGrantButton($id, $status);
        }

        $showNtuButton = $showUndoGrantButton; // display conditions are identical
        $showUndoNtuButton = $this->shouldShowUndoNtuButton($status);

        $sidebarNav->findById('application-decisions-grant')->setVisible($showGrantButton);
        $sidebarNav->findById('application-decisions-undo-grant')->setVisible($showUndoGrantButton);
        $sidebarNav->findById('application-decisions-withdraw')->setVisible($showWithdrawButton);
        $sidebarNav->findById('application-decisions-refuse')->setVisible($showRefuseButton);
        $sidebarNav->findById('application-decisions-not-taken-up')->setVisible($showNtuButton);
        $sidebarNav->findById('application-decisions-undo-not-taken-up')->setVisible($showUndoNtuButton);

        if (!$this->getApplicationService()->canHaveCases($id)) {
            // hide application case link in the navigation
            $this->getNavigationService()->findOneById('application_case')->setVisible(false);
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
        $this->setApplicationService(
            $serviceLocator->get('DataServiceManager')->get('Common\Service\Data\Application')
        );
        $this->setNavigationService($serviceLocator->get('Navigation'));

        return $this;
    }

    protected function shouldShowWithdrawButton($status)
    {
        return ($status === ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION);
    }

    protected function shouldShowRefuseButton($status)
    {
        return ($status === ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION);
    }

    protected function shouldShowGrantButton($status)
    {
        return ($status === ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION);
    }

    protected function shouldShowUndoGrantButton($applicationId, $status)
    {
        $applicationType = $this->getServiceLocator()->get('Entity\Application')->getApplicationType($applicationId);

        if ($applicationType === ApplicationEntityService::APPLICATION_TYPE_NEW
            && $status === ApplicationEntityService::APPLICATION_STATUS_GRANTED
        ) {
            $applicationService = $this->getServiceLocator()->get('Entity\Application');

            $category = $applicationService->getCategory($applicationId);

            return ($category === LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE);
        }

        return false;
    }

    protected function shouldShowUndoNtuButton($status)
    {
        return ($status === ApplicationEntityService::APPLICATION_STATUS_NOT_TAKEN_UP);
    }
}
