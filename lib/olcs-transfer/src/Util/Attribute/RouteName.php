<?php

namespace Dvsa\Olcs\Transfer\Util\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RouteName
{
    public function __construct(protected string $routeName)
    {
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }
}
