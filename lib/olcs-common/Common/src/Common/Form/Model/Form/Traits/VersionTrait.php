<?php

/**
 * Version Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Model\Form\Traits;

use Laminas\Form\Annotation as Form;

/**
 * Version Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait VersionTrait
{
    #[Form\Attributes(['value' => ''])]
    #[Form\Type('Hidden')]
    public $version;
}
