<?php

namespace Olcs\Controller\Listener;

use Common\FeatureToggle;
use Common\Rbac\Service\Permission;
use Common\Rbac\User as RbacUser;
use Common\RefData;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Transfer\Query\Messaging\Messages\UnreadCountByOrganisationAndUser;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Http\Header\Referer as HttpReferer;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Mvc\MvcEvent;
use Laminas\Navigation\Navigation as LaminasNavigation;
use LmcRbacMvc\Service\AuthorizationService;

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
     * @param RbacUser       $identity
     *
     * @return void
     */
    public function __construct(LaminasNavigation $navigation, QuerySender $querySender, protected AuthorizationService $authService)
    {
        $this->navigation = $navigation;
        $this->querySender = $querySender;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, $this->onDispatch(...), 20);
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

        $shouldShowMessagesTab = $this->shouldShowMessagesTab();
        $this->prepareMessagesTab($shouldShowMessagesTab);
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
        if ($this->authService->getIdentity()->isAnonymous()) {
            return false;
        }

        $response = $this->authService->getIdentity()->getUserData();
        return $response['eligibleForPermits'] ?? false;
    }

    /**
     * Check whether the referer is the gov.uk permits page
     *
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
     * Toggle Messaging menus and add count if needed
     *
     * @param bool $shouldShowMessagesTab whether to show messages tab
     *
     * @return void
     */
    private function prepareMessagesTab(bool $shouldShowMessagesTab): void
    {
        $this->navigation->findBy('id', 'dashboard-messaging')
            ->setVisible($shouldShowMessagesTab);

        if ($shouldShowMessagesTab) {
            $this->addUnreadMessagingCount();
        }
    }

    private function shouldShowMessagesTab(): bool
    {
        if (!$this->authService->isGranted(RefData::PERMISSION_CAN_LIST_CONVERSATIONS)) {
            return false;
        };

        $messagingToggleEnabled = $this->querySender->featuresEnabled([FeatureToggle::MESSAGING]);

        $userData = $this->authService->getIdentity()->getUserData();

        $hasOrganisationSubmittedLicenceApplication = $userData['hasOrganisationSubmittedLicenceApplication'];

        $isMessagingEnabled = false;
        if (isset($userData['organisationUsers'][0]['organisation']['isMessagingDisabled'])) {
            $isMessagingEnabled = $userData['organisationUsers'][0]['organisation']['isMessagingDisabled'] === false;
        }

        return (
            $messagingToggleEnabled &&
            $hasOrganisationSubmittedLicenceApplication &&
            $isMessagingEnabled
        );
    }

    /**
     * For the benefit of unit testing
     *
     *
     * @return void
     */
    public function setGovUkReferers(array $govUkReferers): void
    {
        $this->govUkReferers = $govUkReferers;
    }

    public function getUnreadMessageCount(): int
    {
        $userOrganisationId = $this->authService->getIdentity()->getUserData()['organisationUsers'][0]['organisation']['id'];

        $unreadByOrganisation = $this->querySender->send(
            UnreadCountByOrganisationAndUser::create(
                [
                    'organisation' => $userOrganisationId,
                    'user' => $this->authService->getIdentity()->getId(),
                ]
            )
        );

        return($unreadByOrganisation->getResult()['count'] ?? 0);
    }

    public function addUnreadMessagingCount(): void
    {
        $this->navigation->findBy('id', 'dashboard-licences-applications')
            ->findBy('id', 'dashboard-messaging')
            ->set('unreadMessageCount', $this->getUnreadMessageCount());
    }
}
