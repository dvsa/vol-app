<?php

namespace Olcs\Controller\Traits;

/**
 * Class GenerateActionTrait
 * @package Olcs\Controller
 */
trait GenerateActionTrait
{
    abstract protected function getDocumentGenerateRoute();
    abstract protected function getDocumentGenerateRouteParams();

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
