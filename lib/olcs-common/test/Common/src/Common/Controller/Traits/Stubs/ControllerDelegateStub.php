<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Traits\Stubs;

class ControllerDelegateStub
{
    public function indexAction(): string
    {
        return 'return value';
    }
}
