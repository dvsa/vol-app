<?php

declare(strict_types=1);

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("BusNoticePeriod")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class BusNoticePeriod
{
    /**
     * @Form\Name("busNoticePeriod")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\BusNoticePeriod")
     */
    public $busNoticePeriod = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActionsShort")
     */
    public $formActions = null;
}
