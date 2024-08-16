<?php

/**
 * Abstract ID Only Command
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Abstract ID Only Command
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractIdOnlyCommand extends AbstractCommand
{
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}
