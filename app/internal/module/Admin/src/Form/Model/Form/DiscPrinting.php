<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("admin_disc-printing")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class DiscPrinting
{
    /**
     * @Form\Name("operator-location")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\OperatorLocation")
     */
    public $operatorLocation = null;

    /**
     * @Form\Name("operator-type")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\OperatorType")
     */
    public $operatorType = null;

    /**
     * @Form\Name("licence-type")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\LicenceType")
     */
    public $licenceType = null;

    /**
     * @Form\Name("prefix")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\DiscPrefix")
     */
    public $prefix = null;

    /**
     * @Form\Name("discs-numbering")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\DiscNumbering")
     */
    public $discsNumbering = null;

    /**
     * @Form\Name("no-discs")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\NoDiscs")
     */
    public $noDiscs = null;

    /**
     * @Form\Name("queueId")
     * @Form\Attributes({"id":"queueId"})
     * @Form\Type("Hidden")
     */
    public $queueId = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\DiscActions")
     */
    public $formActions = null;
}
