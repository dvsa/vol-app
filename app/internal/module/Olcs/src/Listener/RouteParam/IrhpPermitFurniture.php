<?php

namespace Olcs\Listener\RouteParam;

use Common\RefData;
use Common\Service\Cqrs\Command\CommandSenderAwareInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareTrait;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Common\Util\Escape;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Dvsa\Olcs\Transfer\Query\Permits\ById as EcmtApplicationDto;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById as IrhpApplicationDto;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Navigation\Navigation;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Application;

/**
 * IRHP Permit Furniture
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class IrhpPermitFurniture implements
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
     * @var \Zend\Navigation\Navigation
     */
    protected $navigationService;

    /**
     * @var \Zend\Navigation\Navigation
     */
    protected $sidebarNavigationService;


    /**
     * @var Application
     */
    protected $applicationService;

    /**
     * @return \Zend\Navigation\Navigation
     */
    public function getNavigationService()
    {
        return $this->navigationService;
    }

    /**
     * @param \Zend\Navigation\Navigation $navigationService
     * @return $this
     */
    public function setNavigationService($navigationService)
    {
        $this->navigationService = $navigationService;
        return $this;
    }

    /**
     * Get Sidebar Navigation
     *
     * @return Navigation
     */
    public function getSidebarNavigationService()
    {
        return $this->sidebarNavigationService;
    }

    /**
     * Set Sidebar Navigation
     *
     * @param Navigation $sidebarNavigationService the new navigation service
     *
     * @return $this
     */
    public function setSidebarNavigationService($sidebarNavigationService)
    {
        $this->sidebarNavigationService = $sidebarNavigationService;
        return $this;
    }

    /**
     * @return Application
     */
    public function getApplicationService()
    {
        return $this->applicationService;
    }

    /**
     * @param Application $applicationService
     * @return $this
     */
    public function setApplicationService(Application $applicationService)
    {
        $this->applicationService = $applicationService;
        return $this;
    }

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
        $this->setSidebarNavigationService($serviceLocator->get('right-sidebar'));
        $this->setApplicationService($serviceLocator->get('Application'));

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
            RouteParams::EVENT_PARAM . 'permitid',
            [$this, 'onIrhpPermitFurniture'],
            1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onIrhpPermitFurniture(RouteParam $e)
    {
        $id = $e->getValue();
        $irhpPermit = $this->getIrhpPermit($id);

        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $placeholder->getContainer('irhpPermit')->set($irhpPermit);
        $placeholder->getContainer('status')->set($irhpPermit['status']);
        $placeholder->getContainer('pageTitle')->set($this->getPageTitle($irhpPermit));
        $placeholder->getContainer('pageSubtitle')->set($this->getSubTitle($irhpPermit));
        $placeholder->getContainer('horizontalNavigationId')->set('licence_irhp_permits');

        $this->getNavigationService()->findOneBy('id', 'irhp_permits')
            ->setVisible(true);

        // If the route has a permitId in it then we are editing or viewing related entities (docs etc)
        // and do not wish to have "Add" in the horizonal sub - so disable it..
        $this->getNavigationService()->findOneBy('id', 'licence_irhp_permits-add')
            ->setVisible(false);

        $sidebarNav = $this->getSidebarNavigationService();

        // quick actions
        $sidebarNav->findOneBy('id', 'irhp-permit-quick-actions-cancel')
            ->setVisible($irhpPermit['canBeCancelled']);

        // decisions
        $sidebarNav->findOneBy('id', 'irhp-permit-decisions-submit')
            ->setVisible($irhpPermit['canBeSubmitted']);

        $sidebarNav->findOneBy('id', 'irhp-permit-decisions-withdraw')
            ->setVisible($irhpPermit['canBeWithdrawn']);

        if (isset($irhpPermit['canBeDeclined'])) {
            $sidebarNav->findOneBy('id', 'irhp-permit-decisions-accept')
                ->setVisible($irhpPermit['canBeDeclined']);

            $sidebarNav->findOneBy('id', 'irhp-permit-decisions-decline')
                ->setVisible($irhpPermit['canBeDeclined']);
        }

        $right = new ViewModel();
        $right->setTemplate('sections/irhp-permit/partials/right');

        $placeholder->getContainer('right')->set($right);
    }

    /**
     * Get the Irhp Permit data
     *
     * @param int $id
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getIrhpPermit($id)
    {
        $routeParams = $this->getApplicationService()->getMvcEvent()->getRouteMatch()->getParams();
        $permitTypeId = array_key_exists('permitTypeId', $routeParams) ? intval($routeParams['permitTypeId']) : RefData::ECMT_PERMIT_TYPE_ID;

        if ($permitTypeId === RefData::ECMT_PERMIT_TYPE_ID) {
            $response = $this->getQuerySender()->send(
                EcmtApplicationDto::create(['id' => $id])
            );
        } else {
            $response = $this->getQuerySender()->send(
                IrhpApplicationDto::create(['id' => $id])
            );
        }

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Irhp Permit id [$id] not found");
        }

        return $response->getResult();
    }

    private function getPageTitle($irhpPermit)
    {
        $urlPlugin = $this->getViewHelperManager()->get('Url');
        $licUrl = $urlPlugin->__invoke('licence/permits', ['licence' => $irhpPermit['licence']['id']], [], false);
        return '<a href="' . $licUrl . '">' . $irhpPermit['licence']['licNo'] . '</a>' . '/' . $irhpPermit['id'];
    }

    private function getSubTitle($irhpPermit)
    {
        $typeText = isset($irhpPermit['irhpPermitType']['name']['description']) ? $irhpPermit['irhpPermitType']['name']['description'] : 'ECMT Annual';
        return $irhpPermit['licence']['organisation']['name'] . ', Permit Application - ' . Escape::html($typeText);
    }
}
