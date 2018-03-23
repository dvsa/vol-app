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
     * Provide the route for the next page in the wizard
     *
     * @return array route definition
     */
    abstract protected function getNextPageRoute();

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
        $route = $this->getNextPageRoute();
        return $this->redirect()->toRoute(
            $route['name'],
            $route['params']
        );
    }
}
