<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Licence\Surrender;

use Olcs\Controller\Licence\Surrender\StartController;

final class TestStartController extends StartController
{
    public function setLicenceData(array $licence): void
    {
        $this->action = 'index';
        $this->data = ['licence' => $licence];
    }

    #[\Override]
    protected function shouldRunOnRequest(string $method): bool
    {
        return true;
    }

    #[\Override]
    protected function conditionalDisplayNotMet(string $route): string
    {
        return $route;
    }
}
