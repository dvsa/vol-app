<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait PresidedByRole
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait PresidedByRole
{
    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {"tc_r_dhtru", "tc_r_dtc", "tc_r_htru", "tc_r_tc"}
     *      }
     * )
     */
    protected $presidedByRole;

    /**
     * @return string
     */
    public function getPresidedByRole(): string
    {
        return $this->presidedByRole;
    }
}
