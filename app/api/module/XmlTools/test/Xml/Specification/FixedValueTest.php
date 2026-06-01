<?php

namespace OlcsTest\XmlTools\Xml\Specification;

use Olcs\XmlTools\Xml\Specification\FixedValue;

/**
 * Class FixedValueTest
 * @package OlcsTest\XmlTools\Xml\Specification
 */
class FixedValueTest extends \PHPUnit\Framework\TestCase
{
    public function testApply(): void
    {
        $domDocument = new \DOMDocument();
        $element = $domDocument->createElement('Test');
        $element->nodeValue = 'hello';

        $fixedValue = new FixedValue(['destination', 'as', 'array'], 'value');

        $result = $fixedValue->apply($element);

        $this->assertSame(['destination' => ['as' => ['array' => 'value']]], $result);
    }
}
