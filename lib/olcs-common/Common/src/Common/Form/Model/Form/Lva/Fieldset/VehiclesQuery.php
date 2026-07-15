<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Vehicle Query Data
 */
class VehiclesQuery
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $vrm;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $disc;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $includeRemoved;
}
