<?php

namespace OlcsTest\Controller\Lva\Traits\Stubs;

use Olcs\Controller\Lva\Traits\MethodToggleTrait;

class MethodToggleTraitStub
{
    use MethodToggleTrait;

    protected $methodToggles = [
        'default' => 'some-feature-toggle'
    ];

    public $someMethodString = 'method was not called';

    public function someMethod()
    {
        $this->someMethodString = 'method was called';
    }
}
