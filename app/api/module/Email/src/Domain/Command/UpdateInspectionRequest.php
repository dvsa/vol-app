<?php

/**
 * Update Inspection Request
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Email\Domain\Command;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Update Inspection Request
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateInspectionRequest extends AbstractCommand
{
    protected $id;

    protected $status;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }
}
