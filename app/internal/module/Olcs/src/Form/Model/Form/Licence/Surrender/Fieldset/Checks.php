<?php

namespace Olcs\Form\Model\Form\Licence\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

class Checks
{
    /**
     * @Form\Type("Checkbox")
     * @Form\Options({
     *     "label": "Digital signature has been checked",
     *     "label_options": {
     *          "label_position": "append"
     *     }
     * })
     */
    public $digitalSignature = null;

    /**
     * @Form\Type("Checkbox")
     * @Form\Options({
     *     "label": "ECMS has been checked",
     *     "label_options": {
     *          "label_position": "append"
     *     }
     * })
     */
    public $ecms = null;
}
