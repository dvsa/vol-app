<?php

namespace OlcsTest\XmlTools\Xml\Specification;

use Olcs\XmlTools\Xml\Specification\SpecificationInterface;
use Olcs\XmlTools\Xml\Specification\RecursionValue;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;

/**
 * Class RecursionValueTest
 * @package OlcsTest\XmlTools\Xml\Specification
 */
class RecursionValueTest extends TestCase
{
    public function testApply(): void
    {
        $nodeValue = 'hello';

        $domDocument = new \DOMDocument();
        $element = $domDocument->createElement('Test');
        $element->nodeValue = $nodeValue;

        $recursion = m::mock(SpecificationInterface::class);
        $recursion->shouldReceive('apply')->with($element)->andReturn([$nodeValue]);

        $recursionValue = new RecursionValue('testprop', $recursion);
        $recursionValue->apply($element);

        $result = $recursionValue->apply($element);

        $this->assertEquals(['testprop' => [$nodeValue]], $result);
    }
}
