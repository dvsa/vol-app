<?php

/**
 * Schedule41LicenceSearchLicenceNumber.php
 */
namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("schedule41licencesearchlicencenumber")
 */
class Schedule41LicenceSearchLicenceNumber
{
    /**
     * @Form\Type("Text")
     * @Form\Options({
     *     "label": "Licence number",
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $licenceNumber = null;
}
