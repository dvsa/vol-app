<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("UnderConsideration")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class UnderConsideration
{
    /**
     * @Form\Name("table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\TableRequired")
     * @Form\Options({
     *     "label" : ""
     * })
     * @Form\Attributes({"id":"table"})
     */
    public $table = null;


    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\SubmitWithdraw")
     */
    public $submitButton = null;
}
