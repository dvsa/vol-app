<?php

namespace Dvsa\Olcs\Transfer\Command\Lva;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @author Dmitrij Golubev <dmitrij.golubev@valtech.co.uk>
 */
class AbstractDeleteCompanySubsidiary extends AbstractCommand
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $ids = [];

    /**
     * Get ids
     *
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
