<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use \Dvsa\Olcs\Transfer\Query\Cases\Cases as ItemDto;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;

/**
 * Class Cases
 * @package Olcs\Listener\RouteParam
 */
class Cases implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;

    /**
     * @var \Zend\Navigation\Navigation
     */
    protected $navigationService;

    /**
     * @var \Zend\Navigation\Navigation
     */
    protected $sidebarNavigationService;

    protected $annotationBuilder;

    protected $queryService;

    public function getAnnotationBuilder()
    {
        return $this->annotationBuilder;
    }

    public function getQueryService()
    {
        return $this->queryService;
    }

    public function setAnnotationBuilder($annotationBuilder)
    {
        $this->annotationBuilder = $annotationBuilder;
    }

    public function setQueryService($queryService)
    {
        $this->queryService = $queryService;
    }

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
     * @return \Zend\Navigation\Navigation
     */
    public function getSidebarNavigationService()
    {
        return $this->sidebarNavigationService;
    }

    /**
     * @param \Zend\Navigation\Navigation $sidebarNavigationService
     * @return $this
     */
    public function setSidebarNavigationService($sidebarNavigationService)
    {
        $this->sidebarNavigationService = $sidebarNavigationService;
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
        $this->setAnnotationBuilder($serviceLocator->get('TransferAnnotationBuilder'));
        $this->setQueryService($serviceLocator->get('QueryService'));
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
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'case', [$this, 'onCase'], 1);
    }

    /**
     * @param RouteParam $e
     */
    public function onCase(RouteParam $e)
    {
        $case = $this->getCase($e->getValue());

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('case')->set($case);

        $latestNote = isset($case['latestNote']['comment']) ? $case['latestNote']['comment'] : '';
        $placeholder->getContainer('note')->set($latestNote);

        if (isset($case['licence']['id'])) {
            // Trigger the licence now - it won't trigger twice.
            $e->getTarget()->trigger('licence', $case['licence']['id']);
        }

        if (isset($case['application']['id'])) {
            // Trigger the application now - it won't trigger twice.
            $e->getTarget()->trigger('application', $case['application']['id']);
        }

        if (isset($case['transportManager']['id'])) {
            // If we have a transportManager, get it here.
            $this->getNavigationService()->findOneById('case_opposition')->setVisible(false);
            $this->getNavigationService()->findOneById('case_details_legacy_offence')->setVisible(false);
            $this->getNavigationService()->findOneById('case_details_annual_test_history')->setVisible(false);
            $this->getNavigationService()->findOneById('case_details_prohibitions')->setVisible(false);
            $this->getNavigationService()->findOneById('case_details_statements')->setVisible(false);
            $this->getNavigationService()->findOneById('case_details_conditions_undertakings')->setVisible(false);
            $this->getNavigationService()->findOneById('case_details_impounding')->setVisible(false);
            $this->getNavigationService()->findOneById('case_processing_in_office_revocation')->setVisible(false);

            // Trigger the transportManager now - it won't trigger twice.
            $e->getTarget()->trigger('transportManager', $case['transportManager']['id']);
        } else {
            $this->getNavigationService()->findOneById('case_processing_decisions')->setVisible(false);
        }
    }

    /**
     * Get the Case data
     *
     * @param id $id
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getCase($id)
    {
        // for performance reasons this query should be the same as used in other Case RouteListeners
        $query = $this->getAnnotationBuilder()->createQuery(
            ItemDto::create(['id' => $id])
        );

        $response = $this->getQueryService()->send($query);

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Case id [$id] not found");
        }

        return $response->getResult();
    }
}
