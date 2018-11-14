<?php

namespace Olcs\Form\Model\Form\Surrender;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"method":"post", "id":"surrender-start"})
 * @Form\Type("Common\Form\Form")
 */
class Start
{
    /**
     * @Form\Attributes({"type":"submit","class":"govuk-button govuk-button--start"})
     * @Form\Options({"label":"licence.surrender.start"})
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $submit = null;

    /**
     * @Form\Attributes({"type":"hidden"})
     * @Form\Type("\Zend\Form\Element\Hidden")
     */
    public $status = 'test';
}
