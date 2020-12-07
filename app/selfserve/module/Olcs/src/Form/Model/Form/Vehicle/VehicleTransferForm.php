<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("licence-vehicles")
 * @Form\Attributes({"method":"post", "class":"table__form"})
 * @Form\Type("Common\Form\Form")
 */
class VehicleTransferForm
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
     * @Form\ComposedObject("\Olcs\Form\Model\Form\Vehicle\Fieldset\VehicleTransferFormActions")
     * @Form\Options({"showErrors": true})
     * @Form\Attributes({
     *   "id": "formActions"
     * })
     */
    public $formActions;
}
