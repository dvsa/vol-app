<?php

/**
 * UpdateCompletion
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/single/people/update-completion")
 * @Transfer\Method("POST")
 */
class UpdateCompletion extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"people", "conditionsUndertakings", "taxiPhv", "communityLicences", "transportManagers"}})
     */
    protected $section;

    public function getId()
    {
        return $this->id;
    }

    public function getSection()
    {
        return $this->section;
    }
}
