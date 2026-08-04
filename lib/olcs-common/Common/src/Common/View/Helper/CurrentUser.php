<?php

namespace Common\View\Helper;

use Common\Rbac\User;
use Laminas\View\Helper\AbstractHelper;
use LmcRbacMvc\Service\AuthorizationService;

class CurrentUser extends AbstractHelper
{
    protected $userData;

    /**
     * Construct
     *
     * @param AuthorizationService $authService Authorization service
     *
     * @return void
     */
    public function __construct(private AuthorizationService $authService, private string $userUniqueIdSalt)
    {
    }

    /**
     * Get full name
     *
     * @return string
     */
    public function getFullName()
    {
        if (!$this->isLoggedIn()) {
            return 'Not logged in';
        }

        $userData = $this->getUserData();

        $name = $this->view->personName($userData['contactDetails']['person'], ['forename', 'familyName']);

        if (trim($name) === '' || trim($name) === '0') {
            return $userData['loginId'];
        }

        return $name;
    }

    /**
     * Get organisation name
     *
     * @return string
     */
    public function getOrganisationName()
    {
        if (!$this->isLoggedIn()) {
            return '';
        }

        $userData = $this->getUserData();
        return match ($userData['userType']) {
            User::USER_TYPE_OPERATOR, User::USER_TYPE_TRANSPORT_MANAGER => current($userData['organisationUsers'])['organisation']['name'],
            User::USER_TYPE_PARTNER => $userData['partnerContactDetails']['description'],
            User::USER_TYPE_LOCAL_AUTHORITY => $userData['localAuthority']['description'],
            default => '',
        };
    }

    /**
     * Checks whether the current user is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        $userData = $this->getUserData();
        return (!empty($userData['userType']) && ($userData['userType'] !== User::USER_TYPE_ANON));
    }

    /**
     * Checks whether the current user is an operator
     *
     * @return bool
     */
    public function isOperator()
    {
        $userData = $this->getUserData();
        return (!empty($userData['userType']) && ($userData['userType'] === User::USER_TYPE_OPERATOR));
    }

    /**
     * Checks whether the current user is a local authority
     *
     * @return bool
     */
    public function isLocalAuthority()
    {
        $userData = $this->getUserData();

        return (!empty($userData['userType']) && ($userData['userType'] === User::USER_TYPE_LOCAL_AUTHORITY));
    }

    /**
     * Checks whether the current user is a partner
     *
     * @return bool
     */
    public function isPartner()
    {
        $userData = $this->getUserData();

        return (!empty($userData['userType']) && ($userData['userType'] === User::USER_TYPE_PARTNER));
    }

    public function isTransportManager(): bool
    {
        $userData = $this->getUserData();

        return (!empty($userData['userType']) && ($userData['userType'] === User::USER_TYPE_TRANSPORT_MANAGER));
    }

    /**
     * Get the user's unique id
     * @todo we could look at a micro optimisation here to move this into the identity provider
     * which would mean the need to create the value less often, while also making it available in more places
     * giving us potential to store and create things against the unique user id
     *
     * @return string
     */
    public function getUniqueId()
    {
        if (!$this->isLoggedIn()) {
            return '';
        }

        $userData = $this->getUserData();

        //echo $userData['loginId'] . $this->userUniqueIdSalt;

        return hash('sha256', $userData['loginId'] . $this->userUniqueIdSalt);
    }

    /**
     * Get user data
     *
     * @return array
     */
    private function getUserData()
    {
        if (!$this->userData && $this->authService->getIdentity()) {
            $this->userData = $this->authService->getIdentity()->getUserData();
        }

        return $this->userData;
    }

    /**
     * Get total number of vehicles for operator
     *
     * @return int
     */
    public function getNumberOfVehicles()
    {
        $userData = $this->getUserData();
        return empty($userData['numberOfVehicles']) ? 0 : $userData['numberOfVehicles'];
    }

    /**
     * Is current user internal
     *
     * @return bool
     */
    public function isInternalUser()
    {
        return $this->authService->isGranted('internal-user');
    }
}
