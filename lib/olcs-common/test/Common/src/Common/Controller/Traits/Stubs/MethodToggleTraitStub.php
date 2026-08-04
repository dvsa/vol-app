<?php

namespace CommonTest\Common\Controller\Traits\Stubs;

use Common\Controller\Lva\Traits\MethodToggleTrait;

class MethodToggleTraitStub
{
    use MethodToggleTrait;

    protected $methodToggles = [
        'default' => 'some-feature-toggle'
    ];

    public $someMethodString = 'method was not called';

    public function someMethod(): void
    {
        $this->someMethodString = 'method was called';
    }
}
