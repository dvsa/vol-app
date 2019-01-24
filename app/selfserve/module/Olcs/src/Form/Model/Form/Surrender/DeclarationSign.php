<?php

namespace Olcs\Form\Model\Form\Surrender;

use Zend\Form\Annotation as Form;

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
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({"label":"licence.surrender.declaration.sign"})
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $sign = null;
}
