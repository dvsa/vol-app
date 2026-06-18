<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait NoteType
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait ProhibitionType
{
    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {
     *              "pro_t_si",
     *              "pro_t_sd",
     *              "pro_t_sv",
     *              "pro_t_i",
     *              "pro_t_d",
     *              "pro_t_v",
     *              "pro_t_ro",
     *              "pro_t_vr"
     *          }
     *      }
     * )
     */
    protected $prohibitionType;

    /**
     * @return string
     */
    public function getProhibitionType()
    {
        return $this->prohibitionType;
    }
}
