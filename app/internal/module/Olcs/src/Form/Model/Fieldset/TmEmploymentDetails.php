<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("tm-employment-details")
 */
class TmEmploymentDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"class":"long","id":"position"})
     * @Form\Options({"label":"transport-manager.employment.form.position"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *     "name": "Zend\Validator\StringLength",
     *     "options": {
     *          "max": 45,
     *     },
     * })
     */
    public $position = null;

    /**
     * @Form\Attributes({"class":"long","id":"hoursPerWeek"})
     * @Form\Options({"label":"transport-manager.employment.form.hoursPerWeek"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *     "name": "Zend\Validator\StringLength",
     *     "options": {
     *          "max": 100,
     *     },
     * })
     */
    public $hoursPerWeek = null;
}
