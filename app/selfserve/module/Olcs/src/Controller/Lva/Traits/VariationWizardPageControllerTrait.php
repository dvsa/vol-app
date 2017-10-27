<?php

namespace Olcs\Controller\Lva\Traits;

use Common\Controller\Lva\AbstractController;

trait VariationWizardPageControllerTrait
{
    use ApplicationControllerTrait;

    abstract protected function getVariationType();

    /**
     * Fetch Data for Lva
     *
     * @see AbstractController::fetchDataForLva which will typically provide the implementation
     *
     * @return array|mixed
     */
    abstract protected function fetchDataForLva();

    protected function preDispatch()
    {
        if ($this->isApplicationNew()) {
            return $this->notFoundAction();
        }
        if ($this->fetchDataForLva()['variationType']['id'] !== $this->getVariationType()) {
            return $this->notFoundAction();
        }
        return null;
    }
}
