<?php

/**
 * Create Translate To Welsh Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Task;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Create Translate To Welsh Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateTranslateToWelshTask extends AbstractCommand
{
    protected $description;

    protected $licence;

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getLicence()
    {
        return $this->licence;
    }
}
