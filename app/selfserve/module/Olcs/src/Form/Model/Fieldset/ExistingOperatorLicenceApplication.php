<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("existingOperatorLicenceApplication")
 *
 */
class ExistingOperatorLicenceApplication
{
/**
 *
 * @Form\Required(false)
 * @Form\Type("Text")
 * @Form\Options({
 *     "label": "user-registration.field.licenceNumber.label",
 *     "id": "existingOperatorLicenceApplication",
 * })
 * @Form\Filter("Laminas\Filter\StringTrim")
 * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
 * @Form\Validator({"name": "Laminas\Validator\StringLength", "options": {"min":"9","max": "9"}})
 */
    public $licenceNumber = null;
}
