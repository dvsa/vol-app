<?php

namespace Olcs\Listener\RouteParam;

use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\EventManager\EventInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as ItemDto;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;
use Psr\Container\ContainerInterface;

class Cases implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;

    /**
     * @var \Laminas\Navigation\Navigation
     */
    protected $navigationService;

    /**
     * @var \Laminas\Navigation\Navigation
     */
    protected $sidebarNavigationService;

    protected $annotationBuilder;

    protected $queryService;

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->navigationService = $container->get('navigation');
        $this->annotationBuilder = $container->get(AnnotationBuilder::class);
        $this->queryService = $container->get('QueryService');
        $this->viewHelperManager = $container->get('ViewHelperManager');
        return $this;
    }

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
     * @return \Laminas\Navigation\Navigation
     */
    public function getNavigationService()
    {
        return $this->navigationService;
    }

    /**
     * @param \Laminas\Navigation\Navigation $navigationService
     * @return $this
     */
    public function setNavigationService($navigationService)
    {
        $this->navigationService = $navigationService;
        return $this;
    }

    /**
     * @return \Laminas\Navigation\Navigation
     */
    public function getSidebarNavigationService()
    {
        return $this->sidebarNavigationService;
    }

    /**
     * @param \Laminas\Navigation\Navigation $sidebarNavigationService
     * @return $this
     */
    public function setSidebarNavigationService($sidebarNavigationService)
    {
        $this->sidebarNavigationService = $sidebarNavigationService;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'case',
            [$this, 'onCase'],
            $priority
        );
    }

    public function onCase(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $case = $this->getCase($routeParam->getValue());

        $placeholder = $this->viewHelperManager->get('placeholder');
        $placeholder->getContainer('case')->set($case);

        $latestNote = $case['latestNote']['comment'] ?? '';
        $placeholder->getContainer('note')->set($latestNote);

        if (isset($case['licence']['id'])) {
            // Trigger the licence now - it won't trigger twice.
            $routeParam->getTarget()->trigger('licence', $case['licence']['id']);
        }

        if (isset($case['application']['id'])) {
            // Trigger the application now - it won't trigger twice.
            $routeParam->getTarget()->trigger('application', $case['application']['id']);
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
            $routeParam->getTarget()->trigger('transportManager', $case['transportManager']['id']);
        } else {
            $this->getNavigationService()->findOneById('case_processing_decisions')->setVisible(false);
        }
    }

    /**
     * Get the Case data
     *
     * @param string $id
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
