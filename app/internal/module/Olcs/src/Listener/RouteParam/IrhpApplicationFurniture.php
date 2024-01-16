<?php

namespace Olcs\Listener\RouteParam;

use Common\RefData;
use Common\Service\Cqrs\Command\CommandSenderAwareInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareTrait;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\GetGrantability as GrantabilityDto;
use Laminas\Escaper\Escaper;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Application;

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
     * @var Application
     */
    protected $applicationService;

    /**
     * @return \Laminas\Navigation\Navigation
     */
    public function getNavigationService()
    {
        return $this->navigationService;
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

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->setQuerySender($container->get('QuerySender'));
        $this->setCommandSender($container->get('CommandSender'));
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        $this->setNavigationService($container->get('Navigation'));
        $this->setSidebarNavigationService($container->get('right-sidebar'));
        $this->setApplicationService($container->get('Application'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'irhpAppId',
            [$this, 'onIrhpApplicationFurniture'],
            $priority
        );
    }

    public function onIrhpApplicationFurniture(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $id = $routeParam->getValue();
        $irhpApplication = $this->getIrhpApplication($id);

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('irhpPermit')->set($irhpApplication);
        $placeholder->getContainer('status')->set($irhpApplication['status']);
        $placeholder->getContainer('pageTitle')->set($this->getPageTitle($irhpApplication));
        $placeholder->getContainer('pageSubtitle')->set($this->getSubTitle($irhpApplication));
        $placeholder->getContainer('horizontalNavigationId')->set('licence_irhp_permits-application');

        $sidebarNav = $this->getSidebarNavigationService();
        $mainNav = $this->getNavigationService();
        // quick actions
        $sidebarNav->findOneBy('id', 'irhp-application-quick-actions-cancel')
            ->setVisible($irhpApplication['canBeCancelled']);
        $sidebarNav->findOneBy('id', 'irhp-application-quick-actions-terminate')
            ->setVisible($irhpApplication['canBeTerminated']);
        $sidebarNav->findOneBy('id', 'irhp-application-quick-actions-reset-to-not-yet-submitted-from-cancelled')
            ->setVisible($irhpApplication['canBeResetToNotYetSubmittedFromCancelled']);

        // Enable Link to view full permits if app is in Valid status
        if ($irhpApplication['status']['id'] == RefData::PERMIT_APP_STATUS_VALID) {
            $mainNav->findOneBy('id', 'irhp_permits-permits')
                ->setVisible(true);
        }

        if ($irhpApplication['status']['id'] == RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION
            && $irhpApplication['businessProcess']['id'] == RefData::BUSINESS_PROCESS_APGG
        ) {
            $grantability = $this->getGrantability($irhpApplication);
            $sidebarNav->findOneBy('id', 'irhp-application-decisions-grant')
                ->setVisible($grantability['grantable']);

            // Link to view candidate permits
            $mainNav->findOneBy('id', 'licence_irhp_applications-pregrant')
                ->setVisible(true);

            $mainNav->findOneBy('id', 'irhp_permits-permits')
                ->setVisible(false);
        }

        // decisions
        $sidebarNav->findOneBy('id', 'irhp-application-decisions-submit')
            ->setVisible($irhpApplication['canBeSubmitted']);

        // decline is also done via the withdraw action
        $withdrawVisible = $irhpApplication['canBeWithdrawn'] || $irhpApplication['canBeDeclined'];
        $sidebarNav->findOneBy('id', 'irhp-application-decisions-withdraw')
            ->setVisible($withdrawVisible);

        $sidebarNav->findOneBy('id', 'irhp-application-decisions-revive-from-withdrawn')
            ->setVisible($irhpApplication['canBeRevivedFromWithdrawn']);

        $sidebarNav->findOneBy('id', 'irhp-application-decisions-revive-from-unsuccessful')
            ->setVisible($irhpApplication['canBeRevivedFromUnsuccessful']);

        $sidebarNav->findOneBy('id', 'irhp-application-decisions-reset-to-not-yet-submitted-from-valid')
            ->setVisible($irhpApplication['canBeResetToNotYetSubmittedFromValid']);

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
     * Retrieves grantability information to show/hide grant button
     *
     * @param array $irhpApplication
     * @return array|mixed
     * @throws ResourceNotFoundException
     */
    protected function getGrantability($irhpApplication)
    {
        $response = $this->getQuerySender()->send(
            GrantabilityDto::create(['id' => $irhpApplication['id']])
        );

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Grantability check failed");
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
        $licUrl = $urlPlugin->__invoke('licence/irhp-application', ['licence' => $irhpApplication['licence']['id']], [], false);
        return '<a class="govuk-link" href="' . $licUrl . '">' . $escaper->escapeHtml($irhpApplication['licence']['licNo']) . '</a>' . '/' . $escaper->escapeHtml($irhpApplication['id']);
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
