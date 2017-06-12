<?php

namespace OlcsTest\Controller\Traits\Stub;

use Olcs\Controller\Traits;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class FeesActionTraitStub
{
    use Traits\FeesActionTrait;

    protected function renderLayout($view)
    {
    }

    protected function getFeesRoute()
    {
    }

    protected function getFeesRouteParams()
    {
    }

    protected function getFeesTableParams()
    {
    }
}
