<?php

declare(strict_types=1);

namespace OlcsTest\XmlTools\Xml\Specification;

use Olcs\XmlTools\Xml\Specification\NodeAttribute;
use Olcs\XmlTools\Xml\Specification\NodeValue;
use Olcs\XmlTools\Xml\Specification\Recursion;

/**
 * Also covers iterator classes used internally by recursion algorithm
 */
class RecursionTest extends \PHPUnit\Framework\TestCase
{
    public function testApply(): void
    {
        $domDocument = new \DOMDocument();
        $xml = '<Doc SchemaVersion="2.1"><TestTag>Value</TestTag><OtherTag>this is not the value you are looking for</OtherTag></Doc>';
        $domDocument->loadXML($xml);

        $recursion = new Recursion(['TestTag' => [new NodeValue('testprop')]]);

        $result = $recursion->apply($domDocument->documentElement);

        $this->assertEquals(['testprop' => 'Value'], $result);
    }

    public function testApplyWithShorthands(): void
    {
        $domDocument = new \DOMDocument();
        $xml = '<Doc SchemaVersion="2.1"><TestTag>Value</TestTag><OtherTag>this is not the value you are looking for</OtherTag></Doc>';
        $domDocument->loadXML($xml);

        $recursion = new Recursion('TestTag', new NodeValue('testprop'));

        $result = $recursion->apply($domDocument->documentElement);

        $this->assertEquals(['testprop' => 'Value'], $result);
    }

    public function testApplyWithRootElementAccess(): void
    {
        $domDocument = new \DOMDocument();
        $xml = '<Doc SchemaVersion="2.1"><TestTag>Value</TestTag><OtherTag>this is not the value you are looking for</OtherTag></Doc>';
        $domDocument->loadXML($xml);

        $recursion = new Recursion(
            [
                'TestTag' => new NodeValue('testprop'),
                'Doc' => new NodeAttribute('txcSchemaVersion', 'SchemaVersion'),
            ]
        );

        $result = $recursion->apply($domDocument->documentElement);

        $expected = [
            'testprop' => 'Value',
            'txcSchemaVersion' => '2.1',
        ];

        $this->assertEquals($expected, $result);
    }
}
