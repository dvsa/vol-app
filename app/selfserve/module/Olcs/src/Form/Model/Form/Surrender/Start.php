<?php

namespace Olcs\Form\Model\Form\Surrender;

/**
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class Start
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({"label":"licence.surrender.start"})
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $submit = null;
}
