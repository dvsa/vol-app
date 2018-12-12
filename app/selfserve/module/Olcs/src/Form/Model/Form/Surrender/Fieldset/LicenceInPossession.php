<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-in-possession")
 * @Form\Options({"prefer_form_input_filter":true})
 */
class LicenceInPossession
{
    /**
     * @Form\Options({
     *      "label": "licence.surrender.operator_licence.possession.note",
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $notice = "LicenceInPossession";
}
