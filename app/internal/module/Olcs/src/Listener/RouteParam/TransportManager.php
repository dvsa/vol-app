<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Service\Data\GenericAwareTrait as GenericServiceAwareTrait;
use Olcs\Service\Nr\RestHelper as NrRestHelper;
use Zend\Json\Json;

/**
 * Class Cases
 * @package Olcs\Listener\RouteParam
 */
class TransportManager implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use GenericServiceAwareTrait;
    use ViewHelperManagerAwareTrait;

    /**
     * @var \Olcs\Service\Nr\RestHelper
     */
    protected $nrService;

    /**
     * @var \Zend\Navigation\Navigation
     */
    protected $sidebarNavigation;

    /**
     * @return \Zend\Navigation\Navigation
     */
    public function getSidebarNavigation()
    {
        return $this->sidebarNavigation;
    }

    /**
     * @param \Zend\Navigation\Navigation $sidebarNavigation
     */
    public function setSidebarNavigation($sidebarNavigation)
    {
        $this->sidebarNavigation = $sidebarNavigation;
    }

    /**
     * @return \Olcs\Service\Nr\RestHelper
     */
    public function getNrService()
    {
        return $this->nrService;
    }

    /**
     * @param mixed $nrService
     */
    public function setNrService($nrService)
    {
        $this->nrService = $nrService;
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
            RouteParams::EVENT_PARAM . 'transportManager', [$this, 'onTransportManager'], 1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onTransportManager(RouteParam $e)
    {
        $id = $e->getValue();

        $context = $e->getContext();

        $data = $this->getGenericService()->fetchOne($id);

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('transportManager')->set($data);

        //only show print form link for one controller and action
        if ($context['controller'] == 'TMDetailsResponsibilityController'
             && $context['action'] == 'edit-tm-application'){
             $this->getSidebarNavigation()
                 ->findById('transport_manager_details_review')
                 ->setVisible(true);
        }

        $repute = Json::decode($this->getNrService()->tmReputeUrl($id)->getContent(), Json::TYPE_ARRAY);

        if (isset($repute['Response']['Data']['url'])) {
            $this->getSidebarNavigation()
                 ->findById('transport-manager-quick-actions-check-repute')
                 ->setVisible(true)
                 ->setUri($repute['Response']['Data']['url']);
        }

        $this->doTitles($data);
    }

    public function doTitles($data)
    {
        $this->getViewHelperManager()->get('placeholder')
            ->getContainer('pageTitle')->prepend($this->createTitle($data));
    }

    public function createTitle($data)
    {
        $url = $this->getViewHelperManager()
            ->get('url')
            ->__invoke('transport-manager/details/details', ['transportManager' => $data['id']], [], true);

        $pageTitle = '<a href="'. $url . '">' . $data['homeCd']['person']['forename'] . ' ';
        $pageTitle .= $data['homeCd']['person']['familyName'] . '</a>';

        return $pageTitle;
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

        $this->setGenericService(
            $serviceLocator->get('DataServiceManager')->get('Generic\Service\Data\TransportManager')
        );

        $this->setSidebarNavigation($serviceLocator->get('right-sidebar'));
        $this->setNrService($serviceLocator->get(NrRestHelper::class));

        return $this;
    }
}
