<?php

/**
 * ApproveS4.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Schedule41;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Approve S4 record.
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class ApproveS4 extends AbstractCommand
{
    use Identity;

    protected $status;

    protected $isTrueS4;

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getIsTrueS4()
    {
        return $this->isTrueS4;
    }
}
