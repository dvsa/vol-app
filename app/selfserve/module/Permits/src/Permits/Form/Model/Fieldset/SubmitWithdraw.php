<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmitWithdraw")
 */
class SubmitWithdraw
{
    /**
     * @Form\Attributes({
     *     "value":"We will get back to you by <b>30 November 2018</b>",
     *     "id":"responseBy",
     *     "data-container-class": "govuk-hint guidance-blue extra-space large",
     *     "class": "govuk-hint guidance-blue extra-space large"
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $requiredFinance = null;

    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"submitbutton",
     *     "value":"Withdraw application",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $submit = null;
}
