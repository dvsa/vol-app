<?php

/**
 * Schedule41LicenceSearchLicenceNumber.php
 */
namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
     * @Form\Required(false)
     * @Form\AllowEmpty(false)
     */
    public $licenceNumber = null;
}