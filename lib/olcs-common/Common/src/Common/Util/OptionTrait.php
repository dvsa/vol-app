<?php

namespace Common\Util;

use InvalidArgumentException;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

/**
 * Trait for options.
 *
 * @package Common\Util
 */
trait OptionTrait
{
    protected $options = [];

    /**
     * Sets the options from an array.
     *
     * @param  array|Traversable $options
     *
     * @return OptionTrait
     *
     * @throws InvalidArgumentException
     */
    public function setOptions(array $options)
    {
        // Don't set empty elements
        $options = array_filter($options);

        foreach ($options as $key => $option) {
            $this->setOption($key, $option);
        }

        return $this;
    }

    /**
     * Get defined options by iterating and calling getOption.
     *
     * @return array
     */
    public function getOptions()
    {
        $array = [];

        foreach ($this->options as $key => $value) {
            $array[$key] = $this->getOption($key);
        }

        return $array;
    }

    /**
     * Return the specified option key
     *
     * @param string $key
     *
     * @return NULL|mixed
     */
    public function getOption($key)
    {
        if (!isset($this->options[$key])) {
            return null;
        }

        return $this->options[$key];
    }

    /**
     * Set a single option for an element
     *
     * @param  string $key
     *
     * @return OptionTrait
     */
    public function setOption($key, mixed $value)
    {
        $this->options[$key] = $value;

        return $this;
    }
}
