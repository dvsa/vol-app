<?php

namespace Olcs\Listener;

use Common\Rbac\IdentityProvider;
use Common\Rbac\User;
use Common\RefData;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Laminas\Authentication\AuthenticationService;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Mvc\MvcEvent;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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

        //prevent this from running if the user is not logged in
        if (isset($userData['id'])) {
            // IRHP Permits Navigation
            // Get request params and perform queries only if in licence context
            $irhpPermitsTabEnabled = false;
            $params = $e->getRouteMatch()->getParams();

            if (array_key_exists('licence', $params)) {
                $irhpPermitsTabEnabled = $this->goodsLicenceAndFeatureToggle($params);
            }

            $this->navigation->findBy('id', 'licence_irhp_permits')->setVisible($irhpPermitsTabEnabled);
        }
    }

    /**
     * Query contextual licence to check if goods to render IRHP Permits tab
     *
     * @param array $params request params
     *
     * @return bool
     */
    protected function goodsLicenceAndFeatureToggle($params)
    {
        $licenceQuery = $this->querySender->send(Licence::create(['id' => $params['licence']]));
        $licence = $licenceQuery->getResult();

        return $licence['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_GOODS_VEHICLE;
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
