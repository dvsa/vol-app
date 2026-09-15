<?php

namespace Common\Form\Model\Form\Lva\Fieldset\TransportManager;

use Laminas\Form\Annotation as Form;

class ApplicationDeclaration
{
    /**
     * @Form\Attributes({
     *     "id":"","placeholder":"",
     *     "class": "form",
     *     "value": "Y"
     * })
     * @Form\Options({
     *     "label": "application.signature.options.label",
     *     "label_attributes": {
     *         "class":"form-control form-control--radio form-control--advanced"
     *     },
     *     "value_options": {"Y": "application.signature.options.verify", "N": "application.signature.options.sign"},
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     },
     * })
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $isDigitallySigned;

    /**
     * @Form\Attributes({"id":"declarationDownload"})
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $declarationDownload;

    /**
     * @Form\Attributes({"value": "markup-declaration-for-verify","data-container-class":"declarationForVerify"})
     * @Form\Type("Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $declarationForVerify;
}
