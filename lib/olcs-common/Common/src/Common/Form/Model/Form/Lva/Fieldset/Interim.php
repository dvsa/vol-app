<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("\Common\Form\Elements\Types\RadioVertical")
 * @Form\Options({
 *     "radio-element":"goodsApplicationInterim"
 * })
 */
class Interim
{
    /**
     * @Form\Name("goodsApplicationInterim")
     * @Form\Attributes({"id":"interim[goodsApplicationInterim]","placeholder":""})
     * @Form\Options({
     *     "label": "interim.application.undertakings.form.checkbox.label",
     *     "value_options": {"Y": "Yes", "N": "No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"}
     * })
     * @Form\Attributes({"value": "N"})
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $goodsApplicationInterim;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Form\Lva\Fieldset\AuthorityToOperate")
     */
    public $YContent;
}
