<?php

namespace Olcs\Listener;

use Interop\Container\ContainerInterface;
use Common\Rbac\User;
use Common\RefData;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Mvc\MvcEvent;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Identity\IdentityProviderInterface;

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
     * @var IdentityProviderInterface
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
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
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
        /** @var User $identity */
        $identity = $this->authenticationService->getIdentity();

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
        $goodsOrPsv = $licence['goodsOrPsv']['id'] ?? null;

        return $goodsOrPsv === RefData::LICENCE_CATEGORY_GOODS_VEHICLE;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return NavigationToggle
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : NavigationToggle
    {
        return $this->__invoke($serviceLocator, AnalyticsCookieNamesProvider::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return NavigationToggle
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : NavigationToggle
    {
        $this->navigation = $container->get('navigation');
        $this->authenticationService = $container->get(IdentityProviderInterface::class);
        $this->querySender = $container->get('QuerySender');
        return $this;
    }
}
