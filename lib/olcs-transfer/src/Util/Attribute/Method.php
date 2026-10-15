<?php

namespace Dvsa\Olcs\Transfer\Util\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Method
{
    public function __construct(protected string $method)
    {
    }

    public function getMethod()
    {
        return $this->method;
    }
}
