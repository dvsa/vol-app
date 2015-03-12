<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Zend\Form\Fieldset")
 * @Form\Options({
 *     "label": "admin_disc-printing.discPrefix"
 * })
 */
class DiscPrefix
{
    /**
     * @Form\Attributes({"id": "discSequence"})
     * @Form\Options({"empty_option": "Please Select"})
     * @Form\Name("discSequence")
     * @Form\Type("Select")
     */
    public $discSequence = null;
}
