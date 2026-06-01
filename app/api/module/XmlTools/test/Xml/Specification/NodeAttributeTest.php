<?php

namespace OlcsTest\XmlTools\Xml\Specification;

use Olcs\XmlTools\Xml\Specification\NodeAttribute;

/**
 * Class NodeAttributeTest
 * @package OlcsTest\XmlTools\Xml\Specification
 */
class NodeAttributeTest extends \PHPUnit\Framework\TestCase
{
    public function testApply(): void
    {
        $domDocument = new \DOMDocument();
        $element = $domDocument->createElement('Test');
        $element->setAttribute('id', '74');

        $nodeAttribute = new NodeAttribute('testprop', 'id');

        $result = $nodeAttribute->apply($element);

        $this->assertEquals(['testprop' => '74'], $result);
    }
}
