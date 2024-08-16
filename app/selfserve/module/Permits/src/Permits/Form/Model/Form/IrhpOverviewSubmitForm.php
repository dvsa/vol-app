<?php

namespace Permits\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("IrhpOverviewSubmit")
 * @Form\Attributes({"method":"POST"})
 * @Form\Type("Common\Form\Form")
 */

class IrhpOverviewSubmitForm
{
    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\SubmitApplication")
     */
    public $submitApplicationButton = null;
}
