<?php

namespace Olcs\Event;

use Zend\EventManager\Event;

/**
 * Class RouteParam
 * @package Olcs\Event
 */
class RouteParam extends Event
{
    /**
     * @var string
     */
    protected $value;
    /**
     * @var array
     */
    protected $context;

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
