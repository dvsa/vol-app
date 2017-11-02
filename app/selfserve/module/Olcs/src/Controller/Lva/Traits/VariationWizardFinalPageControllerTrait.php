<?php


namespace Olcs\Controller\Lva\Traits;

trait VariationWizardFinalPageControllerTrait
{
    use VariationWizardPageControllerTrait;

    abstract protected function submitAction();

    protected function goToNextSection($currentSection)
    {
        $response = $this->submitAction();

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }
        $this->goToOverview();
    }
}
