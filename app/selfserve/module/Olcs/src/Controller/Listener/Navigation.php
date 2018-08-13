<?php

namespace Olcs\Controller\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Http\Header\Referer as HttpReferer;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Zend\Navigation\Navigation as ZendNavigation;
use Common\Service\Cqrs\Query\QuerySender;
use Common\FeatureToggle;
use Common\Rbac\User as RbacUser;
use Dvsa\Olcs\Transfer\Query\Organisation\EligibleForPermits;

/**
 * Class Navigation
 * @author Ian Lindsay <ian@hemera-business-services.co.uk
 */
class Navigation implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @var ZendNavigation
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
     * @param ZendNavigation $navigation
     * @param QuerySender    $querySender
     * @param RbacUser       $identity
     *
     * @return void
     */
    public function __construct(ZendNavigation $navigation, QuerySender $querySender, RbacUser $identity)
    {
        $this->navigation = $navigation;
        $this->querySender = $querySender;
        $this->identity = $identity;
    }

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
        $this->toggleEcmtMenus($shouldShowPermitsTab);
        $this->togglePermitsMenus($shouldShowPermitsTab);
    }

    /**
     * Toggle ECMT menus
     *
     * @param bool $shouldShowPermitsTab whether to show permits tab
     *
     * @return void
     */
    private function toggleEcmtMenus(bool $shouldShowPermitsTab): void
    {
        if ($shouldShowPermitsTab) {
            $shouldShowPermitsTab = $this->querySender->featuresEnabled([FeatureToggle::SELFSERVE_ECMT]);
        }

        $this->navigation->findBy('id', 'dashboard-permits')->setVisible($shouldShowPermitsTab);
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
        //permits related config will go here once available
        //$permitsEnabled = $this->querySender->featuresEnabled([FeatureToggle::SELFSERVE_PERMITS]);
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
        //check whether user is allowed to access permits
        $query = EligibleForPermits::create([]);
        $response = $this->querySender->send($query)->getResult();

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
