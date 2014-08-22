<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Public inquiry SLA")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class PublicInquirySLA
{
    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Agreed date",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y",
     *     "category": "defendant_types"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $agreedDate = null;

    /**
     * @Form\Attributes({"id":"dob","class":"long"})
     * @Form\Options({
     *     "label": "Date of PI",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $piDate = null;

    /**
     * @Form\Attributes({"id":"dob","class":"long"})
     * @Form\Options({
     *     "label": "Date of decision",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $decisionDate = null;

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
}
