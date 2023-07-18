<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"method":"post", "id":"vehicle-add"})
 * @Form\Type("Common\Form\Form")
 */
class AddVehicleSearch
{
    /**
     * @Form\Name("vehicle-search")
     * @Form\Options({
     *     "label": "vrm.full",
     *     "shouldEscapeMessages": false,
     * })
     * @Form\Type("\Common\Form\Elements\Types\DvlaVrmSearch")
     */
    public $vrmSearch = null;
}
