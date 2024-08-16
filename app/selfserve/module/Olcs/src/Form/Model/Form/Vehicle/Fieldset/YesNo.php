<?php

namespace Olcs\Form\Model\Form\Vehicle\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("options")
 * @Form\Attributes({
 *     "id":"radio"
 * })
 */
class YesNo
{
    public const OPTION_YES = 'yes';
    public const OPTION_NO = 'no';

    /**
     * @Form\Options({
     *     "label_attributes": {
     *         "class": "form-control form-control--radio form-control--advanced"
     *     },
     *     "value_options": {
     *          "yes": {
     *              "label": "Yes",
     *              "value": "yes",
     *              "attributes": {
     *                  "id":"option-yes"
     *              },
     *          },
     *          "no": {
     *              "label": "No",
     *              "value": "no",
     *              "attributes": {
     *                  "id":"option-no"
     *              },
     *          }
     *      }
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Required(false)
     */
    public $options = null;
}
