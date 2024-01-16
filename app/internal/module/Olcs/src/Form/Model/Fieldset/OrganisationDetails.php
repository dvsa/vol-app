<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("organisation-details")
 * @Form\Options({"label":"Operator details","class":"extra-long"})
 */
class OrganisationDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"Operator name"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $name = null;
}
