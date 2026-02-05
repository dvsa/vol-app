<?php

namespace OlcsTest\Controller\Traits\Stub;

use Common\Service\Helper\FormHelperService;
use Olcs\Controller\Traits;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class FeesActionTraitStub
{
    use Traits\FeesActionTrait;

    public function __construct(protected FormHelperService $formHelper)
    {
    }

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
