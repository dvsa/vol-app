<?php


namespace Olcs\Mvc\Controller\ParameterProvider;

use Zend\Mvc\Controller\Plugin\Params;

/**
 * Interface ParamterProviderInterface
 * @package Olcs\Mvc\Controller\ParameterProvider
 */
interface ParamterProviderInterface
{
    /**
     * @param Params $params
     * @return void
     */
    public function setParams(Params $params);

    /**
     * @return Params
     */
    public function params();

    /**
     * @return array
     */
    public function provideParameters();
}
