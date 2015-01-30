<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("tm-employer-name-details")
 */
class TmEmployerNameDetails
{
    /**
     * @Form\Attributes({"class":"long","id":"position"})
     * @Form\Options({"label":"internal.transport-manager.employment.form.employerName"})
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $employerName = null;
}
