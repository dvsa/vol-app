<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("disqualify")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Disqualify
{
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
     * @Form\Attributes({"id":"name", "class":"extra-long", "readonly":"true"})
     * @Form\Options({
     *     "label": "form.disqualify.name",
     * })
     */
    public $name = null;

    /**
     * @Form\Attributes({"id":"isDisqualified"})
     * @Form\Options({
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "label":"form.disqualify.is-disqualified",
     *     "short-label":"form.disqualify.is-disqualified",
     *  })
     * @Form\Type("OlcsCheckbox")
     */
    public $isDisqualified = null;

    /**
     * @Form\Attributes({"id":"startDate"})
     * @Form\Options({
     *     "label": "form.disqualify.start-date",
     *     "short-label":"form.disqualify.start-date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     * })
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Type("DateSelect")
     * @Form\Required(true)
     */
    public $startDate = null;

    /**
     * @Form\Attributes({"id":"period"})
     * @Form\Options({
     *     "label": "form.disqualify.period",
     * })
     * @Form\Validator("Laminas\Validator\Digits")
     * @Form\Required(false)
     */
    public $period = null;

    /**
     * @Form\Attributes({"id":"notes", "class":"extra-long"})
     * @Form\Type("TextArea")
     * @Form\Options({
     *     "label": "form.disqualify.notes",
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":4000})
     * @Form\Required(false)
     */
    public $notes = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButtons")
     */
    public $formActions = null;
}
