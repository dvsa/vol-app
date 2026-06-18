<?php

/**
 * Abstract Query
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query;

use ReflectionProperty;

/**
 * Abstract Query
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractQuery implements QueryInterface
{
    final public function __construct()
    {
    }

    /**
     * Create instance of a query
     *
     * @param array $data data
     *
     * @return static
     */
    #[\Override]
    public static function create(array $data)
    {
        $command = new static();
        $command->exchangeArray($data);
        return $command;
    }

    /**
     * Exchange internal values from provided array
     *
     * @param array $array array of variables
     *
     * @return void
     */
    #[\Override]
    public function exchangeArray(array $array)
    {
        $values = get_object_vars($this);

        foreach (array_keys($values) as $property) {
            if (isset($array[$property]) && $this->doNotExchange($property) === false) {
                $this->$property = $array[$property];
            }
        }
    }

    /**
     * Return variables as an array
     *
     * @return array
     */
    #[\Override]
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * do not exchange
     *
     * @param string $property property
     *
     * @return bool
     */
    private function doNotExchange($property)
    {
        $reflectionProperty = new ReflectionProperty(static::class, $property);
        $docBlock = $reflectionProperty->getDocComment();
        return str_contains($docBlock, '@Transfer\\DoNotExchange');
    }
}
