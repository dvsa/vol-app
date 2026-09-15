<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * COR certificate number
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
trait CorCertificateNumberOptional
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":12})
     * @Transfer\Optional
     */
    protected $corCertificateNumber;

    /**
     * @return string
     */
    public function getCorCertificateNumber()
    {
        return $this->corCertificateNumber;
    }
}
