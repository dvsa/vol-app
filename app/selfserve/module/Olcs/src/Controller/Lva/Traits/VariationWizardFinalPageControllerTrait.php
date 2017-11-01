<?php


namespace Olcs\Controller\Lva\Traits;

trait VariationWizardFinalPageControllerTrait
{
    use VariationWizardPageControllerTrait;

    abstract protected function handleSubmission();

    protected function goToNextSection($currentSection)
    {
        exit('redirect to next  page' . $this->handleSubmission());
    }
}
