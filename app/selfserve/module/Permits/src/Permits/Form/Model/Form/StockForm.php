<?php
namespace Permits\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("Stock")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class StockForm
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Stock")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\ContinueButton")
     */
    public $submit = null;
}
