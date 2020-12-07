<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("tm-employer-name-details")
 */
class TmEmployerNameDetails
{
    /**
     * @Form\Attributes({"class":"long","id":"position"})
     * @Form\Options({"label":"transport-manager.employment.form.employerName"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator({
     *     "name": "Laminas\Validator\StringLength",
     *     "options": {
     *          "max": 90,
     *     },
     * })
     */
    public $employerName = null;
}
