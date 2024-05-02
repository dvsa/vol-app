<?php

namespace Olcs\Mvc\Controller\ParameterProvider;

use Laminas\Mvc\Controller\Plugin\Params;

/**
 * Interface ParameterProviderInterface
 * @package Olcs\Mvc\Controller\ParameterProvider
 */
interface ParameterProviderInterface
{
    /**
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
