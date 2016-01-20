<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Application Tracking fieldset
 *
 * @note This fieldset is augmented dynamically by the controller based on accessible sections
 * @see Olcs\Controller\Lva\Application\OverviewController
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
