<?php
declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"method":"post", "id":"vehicle-add"})
 * @Form\Type("Common\Form\Form")
 */
class ConfirmVehicle
{
    /**
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     * @Form\Options({"label": "Add vehicle"})
     * @Form\Attributes({
     *     "type": "submit",
     *     "class": "action--secondary",
     * })
     */
    public $confirm = null;
}
