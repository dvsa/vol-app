<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("submission")
 * @Form\Options({"label":""})
 */
class SubmissionTypeSections
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Type("SubmissionSections")
     */
    public $submission_sections = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large"})
     * @Form\Options({
     *     "label": "Select",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $submit = null;

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
}
