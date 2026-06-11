<?php

namespace Olcs\XmlTools\Xml\Specification;

/**
 * Class AbstractCapturingNode
 * @package Olcs\XmlTools\Xml\Specification
 */
abstract class AbstractCapturingNode implements SpecificationInterface
{
    /**
     * @var string|array
     */
    protected $destination;

    /**
     * @return string|array
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param $capturedValue
     */
    protected function createReturnValue($capturedValue): array
    {
        $destination = $this->getDestination();

        if (is_array($destination)) {
            $value = $capturedValue;
            foreach (array_reverse($destination) as $fieldName) {
                $tmp = $value;
                $value = [];
                $value[$fieldName] = $tmp;
            }

            return $value;
        }

        return [$destination => $capturedValue];
    }
}
