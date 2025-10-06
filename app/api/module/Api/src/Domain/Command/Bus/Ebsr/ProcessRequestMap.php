<?php

namespace Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

/**
 * Process Ebsr map request
 */
class ProcessRequestMap extends AbstractIdOnlyCommand
{
    use User;

    protected $scale;

    protected $licence;

    protected $regNo;

    /**
     * Gets the scale
     *
     * @return string
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * Gets the licence id
     *
     * @return int
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Gets the reg no
     *
     * @return string
     */
    public function getRegNo()
    {
        return $this->regNo;
    }

}
