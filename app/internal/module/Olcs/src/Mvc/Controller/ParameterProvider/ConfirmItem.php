<?php

namespace Olcs\Mvc\Controller\ParameterProvider;

/**
 * Class ConfirmItem
 * @package Olcs\Mvc\Controller\ParameterProvider
 */
class ConfirmItem extends AbstractParameterProvider
{
    /**
     * @var array
     */
    private $paramNames;

    private $multi;

    /**
     * @param $paramNames
     */
    public function __construct($paramNames, $multi = false)
    {
        $this->paramNames = (array) $paramNames;
        $this->multi = $multi;
    }

    /**
     * Provides parameters in the same way as item, however explodes all parameters on ,
     * to allow for multiple records to be processed at once
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

        if ($this->multi) {
            return $array;
        }

        return current($array);
    }
}
