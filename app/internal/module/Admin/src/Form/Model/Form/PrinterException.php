<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @Form\Name("PrinterException")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class PrinterException extends Base
{
    /**
     * @Form\Name("exception-details")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\ExceptionDetails")
     */
    public $exceptionDetails = null;

    /**
     * @Form\Name("team-printer")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\TeamPrinter")
     */
    public $teamPrinter = null;

    /**
     * @Form\Name("user-printer")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\UserPrinter")
     */
    public $userPrinter = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActions")
     */
    public $formActions = null;
}
