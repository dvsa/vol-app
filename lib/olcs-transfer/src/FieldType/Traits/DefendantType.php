<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait DefendantType
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait DefendantType
{
    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {
     *              "def_t_dir",
     *              "def_t_driver",
     *              "def_t_op",
     *              "def_t_other",
     *              "def_t_owner",
     *              "def_t_part",
     *              "def_t_tm"
     *          }
     *      }
     * )
     */
    protected $defendantType;

    /**
     * @return string
     */
    public function getDefendantType()
    {
        return $this->defendantType;
    }
}
