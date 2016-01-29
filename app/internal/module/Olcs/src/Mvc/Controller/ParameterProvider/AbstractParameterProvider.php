<?php

namespace Olcs\Mvc\Controller\ParameterProvider;

use Zend\Mvc\Controller\Plugin\Params;

/**
 * Class AbstractParameterProvider
 * @package Olcs\Mvc\Controller\ParameterProvider
 */
abstract class AbstractParameterProvider implements ParameterProviderInterface
{
    /**
     * @var Params
     */
    private $params;

    /**
     * @param Params $params
     */
    public function setParams(Params $params)
    {
        $this->params = $params;
    }

    /**
     * @return Params
     */
    public function params()
    {
        return $this->params;
    }

    final protected function notEmptyOrDefault($item, $default = null)
    {
        return !empty($item) ? $item : $default;
    }
}
