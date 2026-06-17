<?php

/**
 * Delete Printer
 */

namespace Dvsa\Olcs\Transfer\Command\Printer;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/printer/single")
 * @Transfer\Method("DELETE")
 */
final class DeletePrinter extends AbstractDeleteCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $validate;

    public function getValidate()
    {
        return $this->validate;
    }
}
