<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-in-possession")
 */
class LicenceInPossession
{
    /**
     * @Form\AllowEmpty(true)
     * @Form\ContinueIfEmpty(true)
     * @Form\Validator({
     *     "name": "ValidateIf",
     *      "options":{
     *          "context_field": "licenceDocument",
     *          "context_values": {"possession"},
     *          "inject_post_data" : "operatorLicenceDocument->licenceDocument",
     *          "validators": {
     *              {"name": "NotEmpty"}
     *          }
     *      }
     * })
     * @Form\Options({
     *      "label": "licence.surrender.operator_licence.possession.note",
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $notice = "LicenceInPossession";
}
