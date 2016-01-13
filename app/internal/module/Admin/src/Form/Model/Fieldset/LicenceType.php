<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Zend\Form\Fieldset")
 */
class LicenceType
{
    /**
     * @Form\Name("licenceType")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "short-label": "short-label-tol-licence-type",
     *      "fieldset-attributes": {
     *          "id": "licence-type",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "licence-type",
     *      "label": "Licence type",
     *      "value_options":{
     *          "ltyp_r": "Restricted",
     *          "ltyp_sn": "Standard National",
     *          "ltyp_si": "Standard International"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $licenceType = null;
}
