<?php

namespace OlcsTest\XmlTools\Xml\Specification;

use Olcs\XmlTools\Xml\Specification\NodeAttribute;
use Olcs\XmlTools\Xml\Specification\RecursionAttribute;

/**
 * Class RecursionAttributeTest
 *
 * Also covers iterator classes used internally by recursion algorithm
 *
 * @package OlcsTest\XmlTools\Xml\Specification
 */
class RecursionAttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the apply function
     */
    public function testApply(): void
    {
        $value = 'Value';
        $value2 = 'Value2';

        $expectedResult = [
            0 => [
                'value' => $value,
                'value2' => $value2
            ]
        ];

        $spec = [
            new NodeAttribute('value', 'value'),
            new NodeAttribute('value2', 'value2'),
        ];

        $domDocument = new \DOMDocument();
        $xml = '<Doc><TestTag value="' . $value . '" value2="' . $value2 . '"></TestTag>
        <OtherTag value="this is not the value you are looking for"></OtherTag></Doc>';
        $domDocument->loadXML($xml);

        $recursionAttribute = new RecursionAttribute(['TestTag' => $spec]);

        $result = $recursionAttribute->apply($domDocument->documentElement);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the apply function
     */
    public function testApplyWithShorthands(): void
    {
        $value = 'Value';
        $value2 = 'Value2';

        $expectedResult = [
            0 => [
                'value' => $value
            ]
        ];

        $nodeAttribute = new NodeAttribute('value', 'value');

        $domDocument = new \DOMDocument();
        $xml = '<Doc><TestTag value="' . $value . '" value2="' . $value2 . '"></TestTag>
        <OtherTag value="this is not the value you are looking for"></OtherTag></Doc>';
        $domDocument->loadXML($xml);

        $recursionAttribute = new RecursionAttribute('TestTag', $nodeAttribute);

        $result = $recursionAttribute->apply($domDocument->documentElement);

        $this->assertEquals($expectedResult, $result);
    }
}
