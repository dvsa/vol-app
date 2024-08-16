<?php

/**
 * Change Business Type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Organisation;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Change Business Type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ChangeBusinessType extends AbstractCommand
{
    use Identity;

    protected $confirm;

    protected $businessType;

    /**
     * @return mixed
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * @return mixed
     */
    public function getBusinessType()
    {
        return $this->businessType;
    }
}
