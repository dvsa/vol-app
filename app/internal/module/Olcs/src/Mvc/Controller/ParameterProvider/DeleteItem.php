<?php

namespace Olcs\Mvc\Controller\ParameterProvider;

/**
 * Class DeleteItem
 * @package Olcs\Mvc\Controller\ParameterProvider
 */
class DeleteItem extends AbstractParameterProvider
{
    /**
     * @var array
     */
    private $paramNames;

    /**
     * @param $paramNames
     */
    public function __construct($paramNames)
    {
        $this->paramNames = (array) $paramNames;
    }

    /**
     * Provides parameters in the same way as item, however explodes all parameters on , to allow for multi delete
     *
     * @return array
     */
    public function provideParameters()
    {
        $params = [];
        foreach ((array) $this->paramNames as $key => $varName) {
            if (is_int($key)) {
                $params[$varName] = $this->extractMultiples($this->params()->fromRoute($varName));
            } else {
                $params[$key] = $this->extractMultiples($this->params()->fromRoute($varName));
            }
        }

        return $params;
    }

    private function extractMultiples($data)
    {
        $array = explode(',', $data);
        if (count($array) > 1) {
            return $array;
        }

        return current($array);
    }
}
