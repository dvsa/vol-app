<?php

/**
 * Update MyAccount
 */

namespace Dvsa\Olcs\Api\Domain\Command\MyAccount;

use Dvsa\Olcs\Transfer\FieldType\Traits\TranslateToWelshOptional;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Update MyAccount
 */
final class UpdateMyAccount extends AbstractCommand
{
    use TranslateToWelshOptional;

    /**
     * @return int
     */
    protected $id;

    /**
     * @return int
     */
    protected $version;

    /**
     * @return int
     */
    protected $team;

    /**
     * @return string
     */
    protected $loginId;

    /**
     * @return array
     */
    protected $contactDetails;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return int
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @return string
     */
    public function getLoginId()
    {
        return $this->loginId;
    }

    /**
     * @return array
     */
    public function getContactDetails()
    {
        return $this->contactDetails;
    }
}
