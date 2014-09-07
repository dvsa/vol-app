<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Conviction")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class Conviction
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $case = null;

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
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({
     *     "label": "Save",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $conviction = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large"})
     * @Form\Options({
     *     "label": "Cancel",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $cancelConviction = null;

    /**
     * @Form\Name("defendant-details")
     * @Form\Options({"label":"Defendant details"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\DefendantDetails")
     */
    public $defendantDetails = null;

    /**
     * @Form\Name("offence")
     * @Form\Options({"label":"Offence details:","class":"extra-long"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\Offence")
     */
    public $offence = null;
}
