<?php

namespace OlcsTest\XmlTools\Xml\Specification;

use Olcs\XmlTools\Xml\Specification\NodeValue;

/**
 * Class NodeValueTest
 * @package OlcsTest\XmlTools\Xml\Specification
 */
class NodeValueTest extends \PHPUnit\Framework\TestCase
{
    public function testApply(): void
    {
        $domDocument = new \DOMDocument();
        $element = $domDocument->createElement('Test');
        $element->nodeValue = 'hello';

        $nodeValue = new NodeValue('testprop');

        $result = $nodeValue->apply($element);

        $this->assertEquals(['testprop' => 'hello'], $result);
    }
}
