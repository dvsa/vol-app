<?php

namespace Olcs\Mvc\Controller\ParameterProvider;

class AddFormDefaultData extends AbstractParameterProvider
{
    public const FROM_ROUTE = 'route';
    private $config;

    public function __construct($config)
    {
        $this->config = (array) $config;
    }

    public function provideParameters()
    {
        $params = [];

        foreach ((array) $this->config as $key => $value) {
            if ($value === static::FROM_ROUTE) {
                $params[$key] = $this->params()->fromRoute($key);
            } else {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
