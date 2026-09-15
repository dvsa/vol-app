<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait NonPiType
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait NonPiType
{
    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {
     *              "non_pi_type_off_proc",
     *              "non_pi_type_in_cham"
     *          }
     *      }
     * )
     */
    protected $nonPiType;

    /**
     * @return string
     */
    public function getNonPiType()
    {
        return $this->nonPiType;
    }
}
