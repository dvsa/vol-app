<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("data")
 * @Form\Options({"label":"application_taxi-phv_licence-sub-action.data"})
 */
class TaxiPhvLicenceData
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"class":"","id":""})
     * @Form\Options({"label":"application_taxi-phv_licence-sub-action.data.licNo"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $privateHireLicenceNo;
}
