<?php

namespace Olcs\Form\Model\Fieldset;

/**
 * @Form\Name("existing-licence-application")
 */
class ExistingOperatorLicenceApplication
{

/**
 * @Form\Required(true)
 * @Form\Type("Text")
 * @Form\Options({
 *     "label": "user-registration.field.licenceNumber.label",
 *     "id": "existingOperatorLicenceApplication",
 * })
 * @Form\Filter("Laminas\Filter\StringTrim")
 * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
 * @Form\Validator("ValidateIf",
 *      options={
 *          "context_field": "existingOperatorLicence",
 *          "context_values": {"Y"},
 *          "validators": {
 *              {"name": "Laminas\Validator\StringLength", "options": {"min": 2, "max": 35}}
 *          }
 *      }
 * )
 */
public $licenceNumber = null;


}
