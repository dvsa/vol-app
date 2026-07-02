<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("tm-other-licence-details")
 */
class TmOtherLicenceDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $redirectAction;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $redirectId;

    /**
     * @Form\Attributes({"class":"medium","id":"licNo"})
     * @Form\Options({"label":"transport-manager.other-licence.form.lic-no"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength",
     *     options={
     *          "max":18,
     *     },
     * )
     */
    public $licNo;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "transport-manager.other-licence.form.role",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "other_lic_role"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $role;

    /**
     * @Form\Attributes({"class":"long","id":"operatingCentres"})
     * @Form\Options({"label":"transport-manager.other-licence.form.operating-centres"})
     * @Form\Required(true)
     * @Form\Type("Textarea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength",
     *     options={
     *          "max":255,
     *     },
     * )
     */
    public $operatingCentres;

    /**
     * @Form\Attributes({
     *     "class":"short",
     *     "pattern":"\d*",
     *     "id":"totalAuthVehicles"
     * })
     * @Form\Options({"label":"transport-manager.other-licence.form.total-auth-vehicles"})
     * @Form\Required(false)
     * @Form\Validator("Digits")
     * @Form\Type("Text")
     */
    public $totalAuthVehicles;

    /**
     * @Form\Attributes({
     *     "class":"short",
     *     "pattern":"\d(\.)*",
     *     "id":"hoursPerWeek"
     * })
     * @Form\Options({"label":"transport-manager.other-licence.form.hours-per-week"})
     * @Form\Validator("Laminas\Validator\LessThan", options={"max": 99.9,"inclusive":true})
     * @Form\Validator("Laminas\I18n\Validator\IsFloat",
     *     options={
     *         "allow_empty" : false,
     *         "messages": {
     *             "notFloat": "transport-manager.other-licence.form.hours-per-week.error_msg",
     *         }
     *     }
     * )
     * @Form\Type("Text")
     */
    public $hoursPerWeek;
}
