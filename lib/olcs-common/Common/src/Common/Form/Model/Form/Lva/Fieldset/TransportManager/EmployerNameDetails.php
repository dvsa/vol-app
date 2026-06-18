<?php

namespace Common\Form\Model\Form\Lva\Fieldset\TransportManager;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("tm-employer-name-details")
 */
class EmployerNameDetails
{
    /**
     * @Form\Attributes({"class":"long"})
     * @Form\Options({
     *     "label":"transport-manager.employment.form.employerName",
     *     "short-label":"transport-manager.employment.form.employerName"
     * })
     * @Form\Type("Text")
     * @Form\Required(true)
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength",
     *     options={
     *          "max":90,
     *     },
     * )
     */
    public $employerName;
}
