<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Licence")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class LicenceForm
{
    /**
     * @Form\Name("fields")
     * @Form\Options({
     *   "label": "permits.page.licence.question",
     *   "label_attributes": {"class": "visually-hidden"},
     * })
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Licence")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\SubmitOnly")
     */
    public $submit = null;
}
