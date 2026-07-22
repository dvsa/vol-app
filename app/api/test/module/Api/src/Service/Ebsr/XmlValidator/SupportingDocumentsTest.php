<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\XmlValidator;

use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\SupportingDocuments;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class SupportingDocumentsTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\XmlValidator
 */
final class SupportingDocumentsTest extends TestCase
{
    /**
     * @param $xml
     * @param $valid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid(mixed $xml, mixed $valid): void
    {
        vfsStream::setup('root');
        touch(vfsStream::url('root/existing'));

        $context = ['xml_filename' => vfsStream::url('root/xmlfile.xml')];

        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $sut = new SupportingDocuments();

        $this->assertEquals($valid, $sut->isValid($dom, $context));
    }

    public static function isValidProvider(): \Iterator
    {
        yield ['<DocumentUri></DocumentUri>', false];
        yield ['<DocumentUri>notexisting</DocumentUri>', false];
        yield ['<DocumentUri>existing</DocumentUri>', true];
        yield ['<SchematicMap>notexisting</SchematicMap>', false];
        yield ['<SchematicMap>existing</SchematicMap>', true];
        yield ['<SchematicMap></SchematicMap>', false];
    }
}
