<?php

/**
 * Version Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Model\Form\Traits;

/**
 * Version Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait VersionTrait
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;
}
