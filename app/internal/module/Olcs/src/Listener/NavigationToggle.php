<?php

namespace Olcs\Listener;

use Common\Rbac\IdentityProvider;
use Common\Rbac\User;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\Navigation\Navigation;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\Service\Cqrs\Query\QuerySender;
use Common\FeatureToggle;

/**
 * Class NavigationToggle
 * @package Olcs\Listener
 */
class NavigationToggle implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    /**
     * @var Navigation
     */
    protected $navigation;

    /**
     * @var IdentityProvider
     */
    protected $authenticationService;

    /**
     * @var QuerySender
     */
    protected $querySender;

    /**
     * @var array
     */
    protected $listeners = [];

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events Events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 20);
    }

    /**
     * onDispatch - set feature toggle rules here for navigation
     *
     * @param MvcEvent $e Event
     *
     * @return void
     */
    public function onDispatch(MvcEvent $e)
    {
        /** @var AuthenticationService $identity */
        $identity = $this->authenticationService->getIdentity();
        /** @var User $userData */
        $userData = $identity->getUserData();

        $disableDataRetentionRecords = true;

        if (isset($userData['disableDataRetentionRecords'])) {
            $disableDataRetentionRecords = !$userData['disableDataRetentionRecords'];
        }

        $this->navigation
            ->findBy('id', 'admin-dashboard/admin-data-retention')
            ->setVisible($disableDataRetentionRecords);

        $permitsMenuEnabled = $this->querySender->featuresEnabled([FeatureToggle::ADMIN_PERMITS]);

        // Permits Navigation
        $this->navigation->findBy('id', 'admin-dashboard/admin-permits')->setVisible($permitsMenuEnabled);

        // IRHP Permits Navigation
        // Get request params and perform queries only if in licence context
        $irhpPermitsTabEnabled = false;
        $params = $e->getRouteMatch()->getParams();

        if (array_key_exists('licence', $params)) {
            $irhpPermitsTabEnabled = $this->goodsLicenceAndFeatureToggle($params);
        }

        $this->navigation->findBy('id', 'licence_irhp_permits')->setVisible($irhpPermitsTabEnabled);
    }


    /**
     * Query contextual licence to check if goods to render IRHP Permits tab and check Feature Toggle for Internal Permits
     *
     * @param array $params request params
     *
     * @return bool
     */
    protected function goodsLicenceAndFeatureToggle($params)
    {
        $internalPermitsEnabled = $this->querySender->featuresEnabled([FeatureToggle::INTERNAL_PERMITS]);
        $licenceQuery = $this->querySender->send(Licence::create(['id' => $params['licence']]));
        $licence = $licenceQuery->getResult();

        return ($licence['goodsOrPsv']['id'] == 'lcat_gv' && $internalPermitsEnabled);
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->navigation = $serviceLocator->get('navigation');
        $this->authenticationService = $serviceLocator->get('Common\Rbac\IdentityProvider');
        $this->querySender = $serviceLocator->get('QuerySender');

        return $this;
    }
}
