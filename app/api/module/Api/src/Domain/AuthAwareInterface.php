<?php

namespace Dvsa\Olcs\Api\Domain;

use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepoService;

/**
 * Auth Aware Interface
 */
interface AuthAwareInterface
{
    public function setAuthService(AuthorizationService $service);

    /**
     * @return AuthorizationService
     */
    public function getAuthService();

    public function setUserRepository(UserRepoService $service);

    /**
     * @return UserRepoService
     */
    public function getUserRepository();

    /**
     * @param $permission
     * @return bool
     */
    public function isGranted($permission);
}
