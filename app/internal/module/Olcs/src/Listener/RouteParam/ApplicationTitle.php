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
use Common\Service\Data\ApplicationAwareTrait;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Exception\ResourceNotFoundException;

/**
 * Class ApplicationTitle
 * @package Olcs\Listener\RouteParam
 */
class ApplicationTitle implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ApplicationAwareTrait;
    use ViewHelperManagerAwareTrait;

    /**
     * @var \Zend\Navigation\Navigation
     */
    protected $navigationService;

    /**
     * @var \Zend\Navigation\Navigation
     */
    protected $sidebarNavigationService;

    /**
     * @var \Common\Service\Entity\ApplicationEntityService
     */
    protected $applicationEntityService;

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
     * @return \Zend\Navigation\Navigation
     */
    public function getApplicationEntityService()
    {
        return $this->applicationEntityService;
    }

    /**
     * @param \Zend\Navigation\Navigation $applicationEntityService
     * @return $this
     */
    public function setApplicationEntityService($applicationEntityService)
    {
        $this->applicationEntityService = $applicationEntityService;
        return $this;
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
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'application', [$this, 'onApplicationTitle'], 1);
    }

    /**
     * @param RouteParam $e
     */
    public function onApplicationTitle(RouteParam $e)
    {
        $id = $e->getValue();

        $bundle = array(
            'children' => array(
                'status',
                'licence' => array(
                    'children' => array(
                        'organisation'
                    )
                )
            )
        );

        $this->getApplicationService()->setData($id, null);
        $this->getApplicationService()->setId($id);
        $application = $this->getApplicationService()->fetchApplicationData($id, $bundle);

        if (!$application) {
            throw new ResourceNotFoundException("Application id [$id] not found");
        }

        $data = $this->getHeaderParams($application);

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $translator = $this->getViewHelperManager()->get('translate');

        // --
        $html = '<a href="%1$s">%2$s</a> / %3$s';
        $licenceUrl = $this->getRouter()->assemble(['licence' => $data['licenceId']], ['name' => 'licence/cases']);
        $placeholder->getContainer('pageTitle')->set(sprintf($html, $licenceUrl, $data['licNo'], $id));

        // --
        $html = '%1$s <span class="status %2$s">%3$s</span>';
        $pageSubTitle = sprintf($html, $data['companyName'], $data['statusColour'], $translator->__invoke($data['status']));
        $placeholder->getContainer('pageSubtitle')->set($pageSubTitle);
    }

    /**
     * Get headers params
     *
     * @return array
     */
    protected function getHeaderParams($data)
    {
        return array(
            'applicationId' => $data['id'],
            'licNo' => $data['licence']['licNo'],
            'licenceId' => $data['licence']['id'],
            'companyName' => $data['licence']['organisation']['name'],
            'status' => $data['status']['id'],
            'statusColour' => $this->getColourForStatus($data['status']['id']),
        );
    }

    protected function getColourForStatus($status)
    {
        switch ($status) {
            case ApplicationEntityService::APPLICATION_STATUS_VALID:
                $colour = 'green';
                break;
            case ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION:
            case ApplicationEntityService::APPLICATION_STATUS_GRANTED:
                $colour = 'orange';
                break;
            case ApplicationEntityService::APPLICATION_STATUS_WITHDRAWN:
            case ApplicationEntityService::APPLICATION_STATUS_REFUSED:
            case ApplicationEntityService::APPLICATION_STATUS_NOT_TAKEN_UP:
                $colour = 'red';
                break;
            default:
                $colour = 'grey';
                break;
        }

        return $colour;
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
        $this->setApplicationEntityService($serviceLocator->get('Entity\Application'));
        $this->setNavigationService($serviceLocator->get('Navigation'));
        $this->setSidebarNavigationService($serviceLocator->get('right-sidebar'));
        $this->setRouter($serviceLocator->get('Router'));

        return $this;
    }
}
