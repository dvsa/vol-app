<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("change-details")
 */
class ApplicationChangeOfEntityDetails
{
    /**
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Options({"label":"Previous licence No."})
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $oldLicenceNo = null;

    /**
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Options({"label":"Previous operator name"})
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $oldOrganisationName = null;
}