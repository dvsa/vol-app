<?php

namespace Olcs\Mvc\Controller\ParameterProvider;

class GenericList extends AbstractParameterProvider
{
    private $paramNames;
    private $defaultSort;
    private $defaultOrder;

    public function __construct($paramNames, $defaultSort = 'id', $defaultOrder = 'DESC')
    {
        $this->paramNames = (array) $paramNames;
        $this->defaultSort = $defaultSort;
        $this->defaultOrder = $defaultOrder;
    }

    public function provideParameters()
    {
        $params = [
            'page'    => $this->notEmptyOrDefault($this->params()->fromQuery('page'), 1),
            'sort'    => $this->notEmptyOrDefault($this->params()->fromQuery('sort'), $this->defaultSort),
            'order'   => $this->notEmptyOrDefault($this->params()->fromQuery('order'), $this->defaultOrder),
            'limit'   => $this->notEmptyOrDefault($this->params()->fromQuery('limit'), 10),
        ];

        $params = array_merge($this->params()->fromQuery(), $params);

        foreach ($this->paramNames as $key => $varName) {
            if (is_int($key)) {
                $params[$varName] = $this->notEmptyOrDefault($this->params()->fromRoute($varName));
            } else {
                $params[$key] = $this->notEmptyOrDefault($this->params()->fromRoute($varName));
            }
        }

        $params = array_filter($params);

        return $params;
    }
}
