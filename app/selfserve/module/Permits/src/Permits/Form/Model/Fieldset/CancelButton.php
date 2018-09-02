<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("CancelButton")
 */
class CancelButton
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"cancelbutton",
     *     "value":"permits.form.cancel_application.button",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $cancel = null;
}
