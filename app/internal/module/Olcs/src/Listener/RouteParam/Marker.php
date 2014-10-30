<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Navigation\PluginManager as ViewHelperManager;
use Olcs\Service\Marker\CaseMarkers;
use Olcs\Service\Data\Cases as CaseService;

/**
 * Class Marker
 * @package Olcs\Listener\RouteParam
 */
class Marker implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    /**
     * @var CaseMarkers
     */
    protected $caseMarkerService;

    /**
     * @var ViewHelperManager
     */
    protected $viewHelperManager;

    /**
     * @var CaseService
     */
    protected $caseService;

    /**
     * @param \Zend\View\Helper\Navigation\PluginManager $viewHelperManager
     * @return $this
     */
    public function setViewHelperManager($viewHelperManager)
    {
        $this->viewHelperManager = $viewHelperManager;
        return $this;
    }

    /**
     * @return \Zend\View\Helper\Navigation\PluginManager
     */
    public function getViewHelperManager()
    {
        return $this->viewHelperManager;
    }

    /**
     * @param \Olcs\Service\Marker\CaseMarkers $caseMarkerService
     * @return $this
     */
    public function setCaseMarkerService($caseMarkerService)
    {
        $this->caseMarkerService = $caseMarkerService;
        return $this;
    }

    /**
     * @return \Olcs\Service\Marker\CaseMarkers
     */
    public function getCaseMarkerService()
    {
        return $this->caseMarkerService;
    }

    /**
     * @param \Olcs\Service\Data\Cases $caseService
     */
    public function setCaseService($caseService)
    {
        $this->caseService = $caseService;
    }

    /**
     * @return \Olcs\Service\Data\Cases
     */
    public function getCaseService()
    {
        return $this->caseService;
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
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'case', array($this, 'onCase'), 1);
    }

    /**
     * @param RouteParam $e
     */
    public function onCase(RouteParam $e)
    {
        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $case = $this->getCaseService()->fetchCaseData($e->getValue());
        $markers = $this->getCaseMarkerService()->generateMarkerTypes(['appeal', 'stay'], ['case' => $case]);

        $placeholder->getContainer('markers')->set($markers);
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
        $this->setCaseService($serviceLocator->get('DataServiceManager')->get('Olcs\Service\Data\Cases'));

        $caseMarkerService = $serviceLocator
            ->get('Olcs\Service\Marker\MarkerPluginManager')
            ->get('Olcs\Service\Marker\CaseMarkers');

        $this->setCaseMarkerService($caseMarkerService);
        return $this;
    }
}
