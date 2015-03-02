<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"class":"long","id":"licNo"})
     * @Form\Options({"label":"internal.transport-manager.other-licence.form.lic-no"})
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $licNo = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "internal.transport-manager.other-licence.form.role",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "role"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $role = null;

    /**
     * @Form\Attributes({"class":"long","id":"operatingCentres"})
     * @Form\Options({"label":"internal.transport-manager.other-licence.form.operating-centres"})
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $operatingCentres = null;

    /**
     * @Form\Attributes({"class":"long","id":"totalAuthVehicles"})
     * @Form\Options({"label":"internal.transport-manager.other-licence.form.total-auth-vehicles"})
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $totalAuthVehicles = null;

    /**
     * @Form\Attributes({"class":"long","id":"hoursPerWeek"})
     * @Form\Options({"label":"internal.transport-manager.other-licence.form.hours-per-week"})
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $hoursPerWeek = null;
}
