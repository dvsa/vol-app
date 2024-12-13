<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("\Common\Form\Elements\Types\RadioVertical")
 * @Form\Name("existing-operator-licence")
 * @Form\Options({
 *     "radio-element": "existingOperatorLicence"
 * })
 */
class ExistingOperatorLicence
{
    /**
     *
     * @Form\Attributes({
     *      "radios_wrapper_attributes": {"class": "govuk-radios", "data-module":"govuk-radios"}
     *  })
     * @Form\Options({
     *     "value_options":{
     *      "NoLicence":{
     *          "label":"select-option-no",
     *          "value":"N",
     *
     *
     *      },
     *      "licence":{
     *         "label":"select-option-yes",
     *         "attributes": {"data-aira-controls": "conditional-existingOperatorLicenceApplication"},
     *          "value":"Y"
     *       }
     *     },
     *     "label_attributes": {
     *          "class":"form-control form-control--radio form-control--advanced"
     *      },
     * })
     * @Form\Required(true)
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     */
    public $existingOperatorLicence = null;


    /**
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ExistingOperatorLicenceApplication")
     */
    public $licenceContent = null;
}
