<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("licence-vehicles")
 * @Form\Attributes({"method":"post", "class":"table__form"})
 * @Form\Type("Common\Form\Form")
 */
class Vehicles
{
    /**
     * @Form\Name("table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     * @Form\Attributes({
     *   "class": "table"
     * })
     */
    public $table = null;

    /**
     * @Form\ComposedObject("\Olcs\Form\Model\Form\Vehicle\Fieldset\VehicleFormActions")
     * @Form\Options({"showErrors": true})
     */
    public $formActions;
}
