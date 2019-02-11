<?php

namespace Olcs\Listener\RouteParam;

use Common\Service\Cqrs\Command\CommandSenderAwareInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareTrait;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById as ItemDto;
use Zend\Escaper\Escaper;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Navigation\Navigation;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;
use Zend\View\Model\ViewModel;

/**
 * IRHP Application Furniture
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class IrhpApplicationFurniture implements
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
     * @var Navigation
     */
    protected $sidebarNavigationService;

    /**
     * @return \Zend\Navigation\Navigation
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
    public function setSidebarNavigationService(Navigation $sidebarNavigationService)
    {
        $this->sidebarNavigationService = $sidebarNavigationService;
        return $this;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setQuerySender($serviceLocator->get('QuerySender'));
        $this->setCommandSender($serviceLocator->get('CommandSender'));
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setNavigationService($serviceLocator->get('Navigation'));
        $this->setSidebarNavigationService($serviceLocator->get('right-sidebar'));

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
            RouteParams::EVENT_PARAM . 'irhpAppId',
            [$this, 'onIrhpApplicationFurniture'],
            1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onIrhpApplicationFurniture(RouteParam $e)
    {
        $id = $e->getValue();
        $irhpApplication = $this->getIrhpApplication($id);

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('irhpPermit')->set($irhpApplication);
        $placeholder->getContainer('status')->set($irhpApplication['status']);
        $placeholder->getContainer('pageTitle')->set($this->getPageTitle($irhpApplication));
        $placeholder->getContainer('pageSubtitle')->set($this->getSubTitle($irhpApplication));
        $placeholder->getContainer('horizontalNavigationId')->set('licence_irhp_applications');

        $sidebarNav = $this->getSidebarNavigationService();
        // quick actions
        $sidebarNav->findOneBy('id', 'irhp-application-quick-actions-cancel')
            ->setVisible($irhpApplication['canBeCancelled']);
        // decisions
        $sidebarNav->findOneBy('id', 'irhp-application-decisions-submit')
            ->setVisible($irhpApplication['canBeSubmitted']);

        $right = new ViewModel();
        $right->setTemplate('sections/irhp-application/partials/right');

        $placeholder->getContainer('right')->set($right);
    }

    /**
     * Get the Irhp Application data
     *
     * @param int $id
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getIrhpApplication($id)
    {
        $response = $this->getQuerySender()->send(
            ItemDto::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Irhp Permit id [$id] not found");
        }

        return $response->getResult();
    }

    /**
     * @param array $irhpApplication
     * @return string
     */
    private function getPageTitle(array $irhpApplication)
    {
        $urlPlugin = $this->getViewHelperManager()->get('Url');
        $escaper = new Escaper;
        $licUrl = $urlPlugin->__invoke('licence/permits', ['licence' => $irhpApplication['licence']['id']], [], false);
        return '<a href="' . $licUrl . '">' . $escaper->escapeHtml($irhpApplication['licence']['licNo']) . '</a>' . '/' . $escaper->escapeHtml($irhpApplication['id']);
    }

    /**
     * @param array $irhpApplication
     * @return string
     */
    private function getSubTitle(array $irhpApplication)
    {
        return $irhpApplication['licence']['organisation']['name'] . ' - Permit Application - ' . $irhpApplication['irhpPermitType']['name']['description'];
    }
}
