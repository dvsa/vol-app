<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmitConfirm")
 */
class SubmitConfirm
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"govuk-button",
     * })
     * @Form\Options({
     *     "label":"permits.button.confirm-and-continue",
     * })
     * @Form\Type("Zend\Form\Element\Button")
     */
    public $submit = null;
}
