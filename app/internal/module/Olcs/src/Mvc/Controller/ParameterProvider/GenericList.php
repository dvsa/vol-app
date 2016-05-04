<?php

/**
 * GenericList
 */
namespace Olcs\Mvc\Controller\ParameterProvider;

/**
 * GenericList
 */
class GenericList extends AbstractParameterProvider
{
    private $paramNames;
    private $defaultSort;
    private $defaultOrder;

    /**
     * Constructor
     *
     * @param array $paramNames
     * @param string $defaultSort
     * @param string $defaultOrder
     * @return void
     */
    public function __construct($paramNames, $defaultSort = 'id', $defaultOrder = 'DESC')
    {
        $this->paramNames = (array) $paramNames;
        $this->defaultSort = $defaultSort;
        $this->defaultOrder = $defaultOrder;
    }

    /**
     * Provides parameters
     *
     * @return array
     */
    public function provideParameters()
    {
        $params = array_map(
            function ($item) {
                if (is_array($item) && !empty($item['year']) && !empty($item['month']) && !empty($item['day'])) {
                    // looks like a date - convert to string format
                    return $item['year'].'-'.$item['month'].'-'.$item['day'];
                }
                return $item;
            },
            $this->params()->fromQuery()
        );

        $params['page'] = $this->notEmptyOrDefault($this->params()->fromQuery('page'), 1);
        $params['sort'] = $this->notEmptyOrDefault($this->params()->fromQuery('sort'), $this->defaultSort);
        $params['order'] = $this->notEmptyOrDefault($this->params()->fromQuery('order'), $this->defaultOrder);
        $params['limit'] = $this->notEmptyOrDefault($this->params()->fromQuery('limit'), 10);

        foreach ($this->paramNames as $key => $varName) {
            if (is_int($key)) {
                $params[$varName] = $this->notEmptyOrDefault($this->params()->fromRoute($varName));
            } else {
                $params[$key] = $this->notEmptyOrDefault($this->params()->fromRoute($varName));
            }
        }

        return array_filter($params);
    }
}
