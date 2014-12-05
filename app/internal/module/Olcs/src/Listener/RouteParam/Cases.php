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
use Common\Service\Data\Licence as LicenceService;
use Olcs\Service\Data\Cases as CaseService;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;

/**
 * Class Cases
 * @package Olcs\Listener\RouteParam
 */
class Cases implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;

    /**
     * @var LicenceService
     */
    protected $licenceService;

    /**
     * @var CaseService
     */
    protected $caseService;

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
     * @param \Common\Service\Data\Licence $licenceService
     */
    public function setLicenceService($licenceService)
    {
        $this->licenceService = $licenceService;
    }

    /**
     * @return \Common\Service\Data\Licence
     */
    public function getLicenceService()
    {
        return $this->licenceService;
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
        $context = $e->getContext();
        $case = $this->getCaseService()->fetchCaseData($e->getValue());

        $this->getViewHelperManager()->get('headTitle')->prepend('Case ' . $case['id']);

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('pageTitle')->append('Case ' . $case['id']);
        $placeholder->getContainer('pageSubtitle')->append('Case subtitle');

        $placeholder->getContainer('case')->set($case);

        $status = [
            'colour' => $case['closeDate'] !== null ? 'Grey' : 'Orange',
            'value' => $case['closeDate'] !== null ? 'Closed' : 'Open',
        ];

        $placeholder->getContainer('status')->set($status);

        // if we already have licence data, no sense in getting it again.
        if (isset($case['licence']['id'])) {
            $this->getLicenceService()->setData($case['licence']['id'], $case['licence']);

            if (!isset($context['licence'])) {
                $e->getTarget()->trigger('licence', $case['licence']['id']);
            }
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
        $this->setCaseService($serviceLocator->get('DataServiceManager')->get('Olcs\Service\Data\Cases'));
        $this->setLicenceService($serviceLocator->get('DataServiceManager')->get('Common\Service\Data\Licence'));

        return $this;
    }
}
