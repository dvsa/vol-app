<?php

namespace OlcsTest\XmlTools\Xml\Specification;

use Olcs\XmlTools\Xml\Specification\MultiNodeValue;

/**
 * Class MultiNodeValueTest
 * @package OlcsTest\XmlTools\Xml\Specification
 */
class MultiNodeValueTest extends \PHPUnit\Framework\TestCase
{
    public function testApply(): void
    {
        $domDocument = new \DOMDocument();
        $element = $domDocument->createElement('Test');
        $element->nodeValue = 'hello';

        $multiNodeValue = new MultiNodeValue('testprop');

        $result = $multiNodeValue->apply($element);

        $this->assertEquals(['testprop' => ['hello']], $result);
    }
}
