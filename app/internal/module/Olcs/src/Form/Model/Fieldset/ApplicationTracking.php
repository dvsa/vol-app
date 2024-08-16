<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Application Tracking fieldset
 *
 */
class ApplicationTracking
{
    /**
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Type("Hidden")
     */
    public $id = null;
}
