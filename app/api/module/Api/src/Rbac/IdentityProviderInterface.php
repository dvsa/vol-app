<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Rbac;

use LmcRbacMvc\Identity\IdentityProviderInterface as LmcRbacMvcIdentityProviderInterface;

interface IdentityProviderInterface extends LmcRbacMvcIdentityProviderInterface
{
    public const SYSTEM_TEAM = 1;
    public const SYSTEM_USER = 1;

    /**
     * Get masqueraded as system user flag
     *
     * @return bool
     */
    public function getMasqueradedAsSystemUser();

    /**
     * Set masqueraded as system user flag
     *
     *
     * @return void
     */
    public function setMasqueradedAsSystemUser(bool $masqueradedAsSystemUser);
}
