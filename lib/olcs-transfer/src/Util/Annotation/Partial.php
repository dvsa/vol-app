<?php

/**
 * Partial
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Util\Annotation;

use Laminas\Form\Annotation\ComposedObject;

/**
 * @Annotation
 * @NamedArgumentConstructor
 *
 * @mixin ComposedObject
 */
class Partial
{
    protected ComposedObject $composedObject;

    public function __construct(string $value)
    {
        $this->composedObject = new ComposedObject($value);
    }

    public function __call($name, $arguments)
    {
        return $this->composedObject->{$name}($arguments);
    }
}
