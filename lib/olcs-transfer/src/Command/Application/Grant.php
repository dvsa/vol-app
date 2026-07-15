<?php

/**
 * Grant Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/single/grant")
 * @Transfer\Method("PUT")
 */
final class Grant extends AbstractCommand
{
    use Identity;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $shouldCreateInspectionRequest;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"3", "6", "9", "12"}})
     * @Transfer\Optional
     */
    protected $dueDate;

    /**
     * @Transfer\Optional
     */
    protected $notes;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"grant_authority_dl", "grant_authority_tc", "grant_authority_tr"}})
     */
    protected $grantAuthority;

    /**
     * @return mixed
     */
    public function getShouldCreateInspectionRequest()
    {
        return $this->shouldCreateInspectionRequest;
    }

    /**
     * @return mixed
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return string
     */
    public function getGrantAuthority()
    {
        return $this->grantAuthority;
    }
}
