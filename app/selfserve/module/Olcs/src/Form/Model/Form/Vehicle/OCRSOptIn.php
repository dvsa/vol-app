<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("ocrs-opt-in")
 * @Form\Type("Common\Form\Form")
 */
class OCRSOptIn
{
    /**
     * @Form\Type("OlcsCheckbox")
     * @Form\Attributes({"class":"govuk-checkboxes__item"})
     * @Form\Options({
     *     "label":"licence.vehicle.list.form.orcs.checkbox.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     * })
     * @Form\Validator({
     *     "name":"Laminas\Validator\InArray",
     *     "options": {
     *         "haystack": {
     *             "Y", "N"
     *         },
     *         "strict": true,
     *         "messages": {
     *             Laminas\Validator\InArray::NOT_IN_ARRAY: "licence.vehicle.list.form.orcs.checkbox.invalid-value"
     *         }
     *     }
     * })
     */
    public $ocrsCheckbox = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     * })
     * @Form\Options({"label":"licence.vehicle.list.form.orcs.submit.label"})
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $submit = null;
}
