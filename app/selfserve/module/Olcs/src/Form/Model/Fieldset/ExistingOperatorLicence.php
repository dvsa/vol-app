<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("ExistingOperatorLicence")
 * @Form\Attributes({"method":"post"})
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ExistingOperatorLicence
{


    /**
     * @Form\Name("existingOperatorLicence")
     * @Form\Attributes({
     *     "radios_wrapper_attributes": {"class": "govuk-radios--conditional", "data-module":"radios"}
     * })
     * @Form\Attributes({"id":"existingOperatorLicence", "placeholder":"", "required":false})
     * @Form\Options({
     *     "label": "user-registration.field.existing-operator-licence.label",
     *     "label_attributes": {
     *         "class":"form-control form-control--radio form-control--advanced"
     *     },
     *     "hint": "user-registration.field.existing-operator-licence.hint",
     *     "value_options": {
     *          "N": {
     *              "label": "select-option-no",
     *              "value": "N",
     *
     *          },
     *          "Y": {
     *              "label": "select-option-yes",
     *              "value": "Y",
     *              "attributes": {"data-aria-controls":"conditional-", "id":"existingOperatorLicenceApplication"},
     *          }
     *      }
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     * @Form\Required(true)
     */
    public $existingOperatorLicence = null;


    /**
     * @Form\ComposedObject("\Olcs\Form\Model\Form\Fieldset\ExistingLicenceApplicationNumber")
     */
    public $existingLicenceApplicationNumber = null;
}
