<?php

namespace Olcs\Mvc\Controller\ParameterProvider;

class GenericItem extends AbstractParameterProvider
{
    private $paramNames;

    public function __construct($paramNames)
    {
        $this->paramNames = (array) $paramNames;
    }

    public function provideParameters()
    {
        $params = [];

        foreach ((array) $this->paramNames as $key => $varName) {
            if (is_int($key)) {
                $params[$varName] = $this->params()->fromRoute($varName);
            } else {
                $params[$key] = $this->params()->fromRoute($varName);
            }
        }

        return $params;
    }
}
