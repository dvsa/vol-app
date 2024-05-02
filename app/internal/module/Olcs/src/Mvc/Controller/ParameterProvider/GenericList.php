<?php

namespace Olcs\Mvc\Controller\ParameterProvider;

/**
 * GenericList
 */
class GenericList extends AbstractParameterProvider
{
    private $paramNames;
    private $defaultLimit = 10;

    /**
     * Constructor
     *
     * @param array  $paramNames   Param Names
     * @param string $defaultSort  Default sort field
     * @param string $defaultOrder Default order method
     *
     * @return void
     */
    public function __construct($paramNames, private $defaultSort = 'id', private $defaultOrder = 'DESC')
    {
        $this->paramNames = (array) $paramNames;
    }

    /**
     * Set default limit (records per page)
     *
     * @param int $limit records per page
     *
     * @return $this
     */
    public function setDefaultLimit($limit)
    {
        $this->defaultLimit = (int)$limit;

        return $this;
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
                    return $item['year'] . '-' . $item['month'] . '-' . $item['day'];
                }
                return $item;
            },
            $this->params()->fromQuery()
        );

        $params['page'] = $this->notEmptyOrDefault($this->params()->fromQuery('page'), 1);
        $params['sort'] = $this->notEmptyOrDefault($this->params()->fromQuery('sort'), $this->defaultSort);
        $params['order'] = $this->notEmptyOrDefault($this->params()->fromQuery('order'), $this->defaultOrder);
        $params['limit'] = $this->notEmptyOrDefault($this->params()->fromQuery('limit'), $this->defaultLimit);

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
