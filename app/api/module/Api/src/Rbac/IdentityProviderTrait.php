<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Rbac;

trait IdentityProviderTrait
{
    /**
     * @var bool
     */
    private $masqueradedAsSystemUser;

    /**
     * Get masqueraded as system user flag
     *
     * @return bool
     */
    public function getMasqueradedAsSystemUser()
    {
        return $this->masqueradedAsSystemUser;
    }

    /**
     * Set masqueraded as system user flag
     *
     * @param $masqueradedAsSystemUser
     *
     * @return void
     */
    public function setMasqueradedAsSystemUser($masqueradedAsSystemUser)
    {
        $this->masqueradedAsSystemUser = $masqueradedAsSystemUser;
    }
}
