<?php

declare(strict_types=1);

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

    protected function renderLayout(mixed $view): void
    {
    }

    protected function getFeesRoute(): void
    {
    }

    protected function getFeesRouteParams(): void
    {
    }

    protected function getFeesTableParams(): void
    {
    }
}
