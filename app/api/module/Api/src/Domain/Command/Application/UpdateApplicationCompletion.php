<?php

/**
 * Update Application Completion
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Update Application Completion
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateApplicationCompletion extends \Dvsa\Olcs\Transfer\Command\Application\UpdateCompletion
{
    protected $data = [];

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
