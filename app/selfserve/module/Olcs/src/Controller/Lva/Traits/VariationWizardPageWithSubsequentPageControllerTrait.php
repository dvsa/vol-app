<?php

namespace Olcs\Controller\Lva\Traits;

use Zend\Http\Response;
use Zend\Mvc\Controller\Plugin\Redirect;

/**
 * Trait for use in an AbstractController that forms part of a variation wizard but is NOT the final page of a wizard
 *
 * @method Redirect redirect()
 */
trait VariationWizardPageWithSubsequentPageControllerTrait
{
    use VariationWizardPageControllerTrait;

    /**
     * Get the route name for the next page in the wizard
     *
     * @return string
     */
    abstract protected function getNextPageRouteName();

    /**
     * Redirects to the next page in the wizard once this page has been submitted
     *
     * @param string $section current section
     * @param array  $prg     prg
     *
     * @return Response
     */
    protected function completeSection($section, $prg = [])
    {
        return $this->redirect()->toRoute(
            $this->getNextPageRouteName(),
            ['application' => $this->getIdentifier()]
        );
    }
}
