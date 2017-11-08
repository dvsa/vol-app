<?php


namespace Olcs\Controller\Lva\Traits;

trait VariationWizardFinalPageControllerTrait
{
    use VariationWizardPageControllerTrait;

    /**
     * Submit action to be overridden
     *
     * @return mixed
     */
    abstract protected function submit();

    /**
     * go to the next section in the wizard
     *
     * @param string $currentSection current section
     *
     * @return void
     */
    protected function goToNextSection($currentSection)
    {
        $response = $this->submit();

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }
        $this->goToOverview();
    }
}
