<?php

namespace OlcsTest\XmlTools\Filter;

use Olcs\XmlTools\Filter\ParseXmlString;

/**
 * Class ParseXmlStringTest
 * @package OlcsTest\XmlTools\src\Filter
 */
class ParseXmlStringTest extends \PHPUnit\Framework\TestCase
{
    public function testFilter(): void
    {
        $xml = '<test></test>';

        $parseXmlString = new ParseXmlString();

        $dom = $parseXmlString->filter($xml);

        $this->assertInstanceOf('DOMDocument', $dom);
    }
}
