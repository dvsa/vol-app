<?php

/**
 * Id Trait
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Form\Model\Form\Traits;

/**
 * Id Trait
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
trait IdTrait
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;
}
