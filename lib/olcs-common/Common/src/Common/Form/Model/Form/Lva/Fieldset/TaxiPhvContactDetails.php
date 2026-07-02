<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("contactDetails")
 * @Form\Options({"label":"application_taxi-phv_licence-sub-action.contactDetails"})
 */
class TaxiPhvContactDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "application_taxi-phv_licence-sub-action.contactDetails.description",
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $description;
}
