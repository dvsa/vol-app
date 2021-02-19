<?php

namespace Olcs\Controller\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Http\Header\Referer as HttpReferer;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Mvc\MvcEvent;
use Laminas\Navigation\Navigation as LaminasNavigation;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Rbac\User as RbacUser;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount;

/**
 * Class Navigation
 * @author Ian Lindsay <ian@hemera-business-services.co.uk
 */
class Navigation implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @var LaminasNavigation
     */
    protected $navigation;

    /**
     * @var QuerySender
     */
    protected $querySender;

    /**
     * @var RbacUser $identity
     */
    protected $identity;

    /**
     * @var array
     */
    protected $listeners = [];

    /**
     * @todo This is just a placeholder, this will be implemented properly using system parameters in OLCS-20848
     *
     * @var array
     */
    protected $govUkReferers = [];

    /**
     * Navigation constructor
     *
     * @param LaminasNavigation $navigation
     * @param QuerySender    $querySender
     * @param RbacUser       $identity
     *
     * @return void
     */
    public function __construct(LaminasNavigation $navigation, QuerySender $querySender, RbacUser $identity)
    {
        $this->navigation = $navigation;
        $this->querySender = $querySender;
        $this->identity = $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'), 20);
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
        $shouldShowPermitsTab = $this->shouldShowPermitsTab($e);
        $this->togglePermitsMenus($shouldShowPermitsTab);
    }

    /**
     * Toggle EU permits menus
     *
     * @param bool $shouldShowPermitsTab whether to show permits tab
     *
     * @return void
     */
    private function togglePermitsMenus(bool $shouldShowPermitsTab): void
    {
        $this->navigation->findBy('id', 'dashboard-permits')->setVisible($shouldShowPermitsTab);
    }

    /**
     * We need to have either been referred from gov.uk or meet the criteria to be eligible for permits
     * We check the referrer first, as we may be able to save an API call this way
     *
     * @param MvcEvent $e
     *
     * @return bool
     */
    private function shouldShowPermitsTab(MvcEvent $e)
    {
        $referedFromGovUk = $this->referedFromGovUkPermits($e);

        if (!$referedFromGovUk) {
            return $this->isEligibleForPermits();
        }

        return $referedFromGovUk;
    }

    /**
     * Whether the organisation is eligible for permits
     *
     * @return bool
     */
    private function isEligibleForPermits(): bool
    {
        if ($this->identity->isAnonymous()) {
            return false;
        }

        $response = $this->identity->getUserData();
        return $response['eligibleForPermits'];
    }

    /**
     * Check whether the referer is the gov.uk permits page
     *
     * @param MvcEvent $e
     *
     * @return bool
     */
    private function referedFromGovUkPermits(MvcEvent $e): bool
    {
        /**
         * @var HttpRequest $request
         * @var HttpReferer|bool $referer
         */
        $request = $e->getRequest();
        $referer = $request->getHeader('referer');

        if (!$referer instanceof HttpReferer) {
            return false;
        }

        return in_array($referer->getUri(), $this->govUkReferers);
    }

    /**
     * For the benefit of unit testing
     *
     * @param array $govUkReferers
     *
     * @return void
     */
    public function setGovUkReferers(array $govUkReferers): void
    {
        $this->govUkReferers = $govUkReferers;
    }
}
