<?php

namespace OlcsTest\XmlTools\Xml\Specification;

use Olcs\XmlTools\Xml\Specification\SpecificationInterface;
use Olcs\XmlTools\Xml\Specification\MultiRecursionValue;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;

/**
 * Class MultiRecursionValueTest
 * @package OlcsTest\XmlTools\Xml\Specification
 */
class MultiRecursionValueTest extends TestCase
{
    public function testApply(): void
    {
        $nodeValue = 'hello';
        $expectedResult = [
            'testprop' => [
                0 => [$nodeValue]
            ]
        ];

        $domDocument = new \DOMDocument();
        $element = $domDocument->createElement('Test');
        $element->nodeValue = $nodeValue;

        $recursion = m::mock(SpecificationInterface::class);
        $recursion->shouldReceive('apply')->twice()->with($element)->andReturn([$nodeValue]);

        $multiRecursionValue = new MultiRecursionValue('testprop', $recursion);
        $multiRecursionValue->apply($element);

        $result = $multiRecursionValue->apply($element);

        $this->assertEquals($expectedResult, $result);
    }
}
