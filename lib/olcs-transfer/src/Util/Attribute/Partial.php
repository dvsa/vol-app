<?php

namespace Dvsa\Olcs\Transfer\Util\Attribute;

use Attribute;
use Laminas\Form\Annotation\ComposedObject;

#[Attribute(Attribute::TARGET_PROPERTY)]
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
