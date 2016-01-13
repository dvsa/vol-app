<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("document")
 */
class Document
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $formName = null;

    /**
     * @Form\Options({
     *     "checked_value": 1,
     *     "unchecked_value": 0,
     *     "must_be_checked": false,
     *     "label": "Generate document"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $generate = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $templateId = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $countryCode = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $format = null;
}
