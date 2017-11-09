<?php


namespace Olcs\Controller\Lva\Traits;

use Zend\Http\Response;

/**
 * Trait for use in an AbstractController that forms the final part of a variation wizard
 */
trait VariationWizardFinalPageControllerTrait
{
    use VariationWizardPageControllerTrait;

    /**
     * Submit action to be overridden
     *
     * @return Response
     */
    abstract protected function submit();

    /**
     * go to the next section in the wizard
     *
     * @param string $currentSection current section
     *
     * @return Response
     */
    protected function goToNextSection($currentSection)
    {
        return $this->submit();
    }
}
