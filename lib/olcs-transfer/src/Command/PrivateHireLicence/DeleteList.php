<?php

/**
 * Delete one or more PrivateHireLicence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\PrivateHireLicence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/private-hire-licence")
 * @Transfer\Method("DELETE")
 */
final class DeleteList extends AbstractCommand
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $ids = [];

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $licence;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"application","variation","licence"}})
     * @Transfer\Optional
     */
    protected $lva;

    /**
     * Get TM Employment ID's
     *
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * @return int
     */
    public function getLicence()
    {
        return $this->licence;
    }

    public function getLva()
    {
        return $this->lva;
    }
}
