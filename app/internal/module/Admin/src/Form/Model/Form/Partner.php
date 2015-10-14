<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Partner")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Partner
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":"Partner Name"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\PartnerName")
     */
    public $fields = null;

    /**
     * @Form\Name("address")
     * @Form\Options({"label":"Address"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     */
    public $address = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
