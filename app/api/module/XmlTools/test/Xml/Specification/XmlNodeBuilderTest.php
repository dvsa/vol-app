<?php

namespace OlcsTest\XmlTools\Xml;

use Olcs\XmlTools\Xml\XmlNodeBuilder;

/**
 * Class XmlNodeBuilderTest
 * @package XmlTools\src\Xml
 */
class XmlNodeBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testBuildTemplate(): void
    {
        $parentElement = 'ParentElement';
        $ns = 'https://webgate.ec.testa.eu/erru/1.0';

        $input = [
            'Body' => [
                'name' => 'Body',
                'attributes' => [
                    'attributeOne' => 'FirstAttribute',
                    'attributeTwo' => 'SecondAttribute',
                ],
                'nodes' => [
                    0 => [
                        'name' => 'FirstNode',
                        'value' => 'valueOne'
                    ],
                    1 => [
                        'name' => 'SecondNode',
                        'value' => 'valueTwo'
                    ]
                ]
            ]
        ];

        $expectedReturn = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<' . $parentElement . ' xmlns="' . $ns . '">' . "\n" .
            '  <Body attributeOne="FirstAttribute" attributeTwo="SecondAttribute">' . "\n" .
            '    <FirstNode>valueOne</FirstNode>' . "\n" .
            '    <SecondNode>valueTwo</SecondNode>' . "\n" .
            '  </Body>' . "\n" .
            '</' . $parentElement . '>';

        $xmlNodeBuilder = new XmlNodeBuilder($parentElement, $ns, $input);
        $this->assertXmlStringEqualsXmlString($expectedReturn, $xmlNodeBuilder->buildTemplate());
    }
}
