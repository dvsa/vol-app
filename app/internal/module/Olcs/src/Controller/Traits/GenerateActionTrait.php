<?php

namespace Olcs\Controller\Traits;

/**
 * Class GenerateActionTrait
 * @package Olcs\Controller
 */
trait GenerateActionTrait
{
    protected abstract function getDocumentGenerateRoute();
    protected abstract function getDocumentGenerateRouteParams();

    /**
     * Generate action.
     */
    public function generateAction()
    {
        $params = $this->getDocumentGenerateRouteParams();
        $route  = $this->getDocumentGenerateRoute();
        return $this->redirect()->toRoute($route, $params);
    }
}
