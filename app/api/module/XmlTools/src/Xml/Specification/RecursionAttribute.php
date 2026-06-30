<?php

namespace Olcs\XmlTools\Xml\Specification;

use Olcs\XmlTools\Xml\ElementIterator;
use Olcs\XmlTools\Xml\NodeListIterator;
use Olcs\XmlTools\Xml\TagNameFilterIterator;
use Laminas\Stdlib\ArrayUtils;

/**
 * Class RecursionAttribute
 * @package Olcs\XmlTools\Xml\Specification
 */
class RecursionAttribute implements SpecificationInterface
{
    /**
     * @var array
     */
    protected $specification = [];

    /**
     * @param $specification
     */
    public function __construct($specification, $instructions = null)
    {
        if (is_array($specification)) {
            $this->specification = $specification;
        } elseif (is_string($specification) && $instructions !== null) {
            $this->specification = [$specification => $instructions];
        }
    }

    /**
     * @return array
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @return array
     */
    #[\Override]
    public function apply(\DOMElement $domElement)
    {
        $result = [];
        $count = 0;

        $specification = $this->getSpecification();
        $nodeList = $domElement->childNodes;

        $iterator = new NodeListIterator($nodeList);
        $iterator = new ElementIterator($iterator);
        $iterator = new TagNameFilterIterator($iterator, array_keys($specification));

        /** @var \DOMElement $element */
        foreach ($iterator as $element) {
            $result[$count] = [];
            $spec = $specification[$element->tagName];

            if (!is_array($spec)) {
                $spec = [$spec];
            }

            foreach ($spec as $instruction) {
                /** @var SpecificationInterface $instruction */
                $result[$count] = ArrayUtils::merge($result[$count], $instruction->apply($element));
            }

            ++$count;
        }

        return $result;
    }
}
