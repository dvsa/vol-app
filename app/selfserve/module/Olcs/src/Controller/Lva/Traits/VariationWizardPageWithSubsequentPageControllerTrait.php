<?php

namespace Olcs\Controller\Lva\Traits;

use Zend\Mvc\Controller\Plugin\Redirect;

/**
 * @method Redirect redirect()
 */
trait VariationWizardPageWithSubsequentPageControllerTrait
{
    use VariationWizardPageControllerTrait;

    abstract protected function getNextPageRouteName();

    protected function completeSection($section, $prg = [])
    {
        return $this->redirect()->toRoute(
            $this->getNextPageRouteName(),
            ['application' => $this->getIdentifier()]
        );
    }
}
