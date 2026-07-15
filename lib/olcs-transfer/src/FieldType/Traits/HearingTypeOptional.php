<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait HearingType
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait HearingTypeOptional
{
    /**
     * @var String
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {
     *              "non_pi_type_stl_interview",
     *              "non_pi_type_pre_hearing"
     *          }
     *      }
     * )
     */
    protected $hearingType;

    /**
     * @return string
     */
    public function getHearingType()
    {
        return $this->hearingType;
    }
}
