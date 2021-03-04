<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("vehicle-search")
 * @Form\Attributes({"method":"get","class":"filters form__search"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ListVehicleSearch
{
    const FIELD_VEHICLE_SEARCH = 'vehicleSearch';

    /**
     * @Form\Name("vehicleSearch")
     * @Form\Options({
     *     "label": "licence.vehicle.table.search.label",
     *     "legend": "licence.vehicle.table.search.legend",
     * })
     * @Form\Type("\Common\Form\Elements\Types\VehicleTableSearch")
     */
    public $vrmSearch = null;
}
