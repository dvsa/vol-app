<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("vehicle-search")
 * @Form\Attributes({"method":"get","class":"filters form__search"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class VehicleSearch
{
    /**
     * @Form\Name("vehicleSearch")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\VehicleSearch")
     */
    public $vehiclesSearch;

    /**
     * @Form\Type("Hidden")
     */
    public $limit;

    /**
     * @Form\Type("Hidden")
     */
    public $includeRemoved;
}
