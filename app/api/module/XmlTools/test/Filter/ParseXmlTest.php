<?php

namespace OlcsTest\XmlTools\Filter;

use Olcs\XmlTools\Filter\ParseXml;
use org\bovigo\vfs\vfsStream;

/**
 * Class ParseXmlTest
 * @package OlcsTest\XmlTools\src\Filter
 */
class ParseXmlTest extends \PHPUnit\Framework\TestCase
{
    public function testFilter(): void
    {
        $xml = '<test></test>';

        vfsStream::setup('root');
        $xmlFile = vfsStream::url('root/xmlfile.xml');

        file_put_contents($xmlFile, $xml);

        $parseXml = new ParseXml();

        $dom = $parseXml->filter($xmlFile);

        $this->assertInstanceOf('DOMDocument', $dom);
    }
}
