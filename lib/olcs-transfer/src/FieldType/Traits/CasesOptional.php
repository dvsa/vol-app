<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait CasesOptional
 *
 * Only called Cases because Case is a reserved word. Still works as if it was called Case.
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait CasesOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $case;

    /**
     * Get case
     *
     * @return int
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Set case
     *
     * @param int $caseId case id
     *
     * @return void
     */
    public function setCase($caseId)
    {
        $this->case = (int) $caseId;
    }
}
