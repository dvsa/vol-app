<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("ChangeButton")
 */
class ChangeButton
{
    /**
     * @Form\Name("ChangeButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"cancelbutton",
     *     "value":"permits.form.change_licence.button",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $change = null;
}
