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
     * Finish the wizard
     *
     * @param string $section section
     * @param array  $prg     prg
     *
     * @return Response
     */
    protected function completeSection($section, $prg = [])
    {
        return $this->submit();
    }
}
