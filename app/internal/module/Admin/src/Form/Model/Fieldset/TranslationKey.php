<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Zend\Form\Annotation as Form;

/**
 * ID only fieldset for Javascript population of related Keys
 *
 * @Form\Type("Zend\Form\Fieldset")
 * @Form\Attributes({"class":"translation-keys"})
 */
class TranslationKey
{
    /**
     * @Form\Attributes({"id":"id"})
     * @Form\Type("Hidden")
     */
    public $id = null;
}
