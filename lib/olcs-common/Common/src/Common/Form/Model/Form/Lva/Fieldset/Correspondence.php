<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"label":"application_your-business_business-type.correspondence.label"})
 */
class Correspondence
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
     *    "label":"application_your-business_fao.label",
     *    "label_attributes": {
     *        "aria-label": "application_your-business_fao.label"
     *    }
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Validator("Laminas\Validator\StringLength",
     *     options={
     *         "min": 0,
     *         "max": 90,
     *     }
     * )
     */
    public $fao;
}
