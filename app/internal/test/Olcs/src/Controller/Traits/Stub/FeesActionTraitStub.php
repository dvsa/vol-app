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

    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        $this->formHelper = $formHelper;
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
