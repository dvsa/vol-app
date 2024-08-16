<?php

namespace Olcs\Form\Model\Form\Surrender;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"method":"post", "class":"form"})
 * @Form\Type("\Common\Form\Form")
 */
class DeclarationSign
{
    /**
     * @Form\Attributes({"value": "licence.surrender.declaration.form.header"})
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $header = null;

    /**
     * @Form\Attributes({"value": "markup-declaration-for-verify","data-container-class":"declarationForVerify"})
     * @Form\Type("Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $declaration = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label":"licence.surrender.declaration.sign"})
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $sign = null;
}
