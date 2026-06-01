<?php

declare(strict_types=1);

namespace OlcsTest\XmlTools\Validator;

use Olcs\XmlTools\Validator\Xsd;
use org\bovigo\vfs\vfsStream;
use DOMDocument;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class XsdTest
 * @package OlcsTest\XmlTools\src\Validator
 */
class XsdTest extends TestCase
{
    protected $xsd = <<<XSD
<?xml version="1.0" encoding="UTF-8" ?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
 <xs:element name="test" type="xs:string" />
</xs:schema>
XSD;

    /**
     * Tests what happens when the document doesn't validate against the schema
     * We ask for a maximum of 2 errors, and have excluded errors 2 and 3 through setXmlMessageExclude
     * Therefore errors 1 and 4 are what we return
     */
    public function testIsValidFalse(): void
    {
        libxml_clear_errors();
        $xml = '<doc><test></test><test></test></doc>';
        $maxErrors = 2; //we should get 3 errors from libxml, so this makes sure we're only returning the first 2

        // Edited for backwards compatibility - See expectedErrorCount below
        // $totalErrors = 3; //accounted for by the generic error that precedes the specific xml

        $error = new \LibXMLError();
        $error->message = '*error message*';
        $error->line = 111;
        $error->column = 222;

        $error2 = new \LibXMLError();
        $error2->message = '*error message 2*';
        $error2->line = 333;
        $error2->column = 444;

        $error3 = new \LibXMLError();
        $error3->message = '*error message 3*';
        $error3->line = 555;
        $error3->column = 666;

        $error4 = new \LibXMLError();
        $error4->message = '*error message 4*';
        $error4->line = 777;
        $error4->column = 888;

        $error5 = new \LibXMLError();
        $error5->message = '*error message 5*';
        $error5->line = 999;
        $error5->column = 101010;

        $libXmlErrors = [
            0 => $error,
            1 => $error2,
            2 => $error3,
            3 => $error4,
            4 => $error5
        ];

        vfsStream::setup('root');
        $xsdFile = vfsStream::url('root/xsdfile.xsd');

        file_put_contents($xsdFile, $this->xsd);

        $domDocument = new DOMDocument();
        $domDocument->loadXml($xml);

        /** @var Xsd|m\MockInterface $sut */
        $sut = m::mock(Xsd::class)->makePartial();
        $sut->setMappings(
            [
                'http://schemafile.com/xsdfile.xsd' => $xsdFile,
                'http://www.w3.org/2001/XMLSchema' => __DIR__ . '/../../../../data/xsd/xml.xsd'
            ]
        );

        $sut->setMaxErrors($maxErrors);
        $sut->setXsd('http://schemafile.com/xsdfile.xsd');

        $sut->setXmlMessageExclude(['message 2', 'message 3']); //message 2 and 3 are excluded

        $sut->expects('getXmlErrors')->andReturn($libXmlErrors);
        $this->assertEquals(false, $sut->isValid($domDocument));

        $messages = $sut->getMessages();
        $expectedMessage1 = 'XML error "*error message*" on line 111 column 222';
        $expectedMessage2 = 'XML error "*error message 4*" on line 777 column 888';

        // laminas-validator 2.11.1 will include an extra message 'invalid-xml', 2.25.0 will not
        $expectedErrorCount = array_key_exists('invalid-xml', $messages) ? 3 : 2;
        $this->assertCount($expectedErrorCount, $messages);
        $this->assertContains($expectedMessage1, $messages);
        $this->assertContains($expectedMessage2, $messages);
    }

    /**
     * Tests what happens when the document doesn't validate against the schema,
     * but all errors are excluded due to containing paths
     */
    public function testIsValidFalseNoExtraMessages(): void
    {
        libxml_clear_errors();
        $xml = '<doc><test></test><test></test></doc>';

        $error = new \LibXMLError();
        $error->message = '*error message*';
        $error->line = 333;
        $error->column = 444;

        $error2 = new \LibXMLError();
        $error2->message = '*error message 2*';
        $error2->line = 555;
        $error2->column = 666;

        $libXmlErrors = [
            0 => $error,
            1 => $error2
        ];

        vfsStream::setup('root');
        $xsdFile = vfsStream::url('root/xsdfile.xsd');

        file_put_contents($xsdFile, $this->xsd);

        $domDocument = new DOMDocument();
        $domDocument->loadXml($xml);

        /** @var Xsd|m\MockInterface $sut */
        $sut = m::mock(Xsd::class)->makePartial();
        $sut->setMappings(
            [
                'http://schemafile.com/xsdfile.xsd' => $xsdFile,
                'http://www.w3.org/2001/XMLSchema' => __DIR__ . '/../../../../data/xsd/xml.xsd'
            ]
        );

        $sut->setXsd('http://schemafile.com/xsdfile.xsd');

        $sut->setXmlMessageExclude(['error message']); //will exclude both messages

        $sut->expects('getXmlErrors')->andReturn($libXmlErrors);
        $this->assertEquals(false, $sut->isValid($domDocument));

        $messages = $sut->getMessages();

        // laminas-validator 2.11.1 will include an extra message 'invalid-xml-no-error', 2.25.0 will not
        $expectedErrorCount = array_key_exists('invalid-xml-no-error', $messages) ? 1 : 0;
        $this->assertCount($expectedErrorCount, $messages);
        // $this->assertArrayHasKey('invalid-xml-no-error', $messages);
    }

    /**
     * @dataProvider provideIsValid
     * @param $xml
     */
    public function testIsValid($xml): void
    {
        libxml_clear_errors();
        vfsStream::setup('root');
        $xsdFile = vfsStream::url('root/xsdfile.xsd');

        file_put_contents($xsdFile, $this->xsd);

        $domDocument = new DOMDocument();
        $domDocument->loadXml($xml);

        $xsd = new Xsd();
        $xsd->setMappings(
            [
                'http://schemafile.com/xsdfile.xsd' => $xsdFile,
                'http://www.w3.org/2001/XMLSchema' => __DIR__ . '/../../../../data/xsd/xml.xsd'
                ]
        );
        $xsd->setXsd('http://schemafile.com/xsdfile.xsd');
        $this->assertEquals(true, $xsd->isValid($domDocument));
    }

    public function provideIsValid()
    {
        return [
            ['<test></test>'],
            ['<test>Hello</test>']
        ];
    }

    public function testIsValidWithoutMapping(): void
    {
        libxml_clear_errors();
        $domDocument = new DOMDocument();
        $domDocument->loadXML('<test></test>');

        $valid = true;

        vfsStream::setup('root');
        $xsdFile = vfsStream::url('root/xsdfile.xsd');

        file_put_contents($xsdFile, $this->xsd);

        $xsd = new Xsd();
        $xsd->setMappings(
            ['http://www.w3.org/2001/XMLSchema' => __DIR__ . '/../../../../data/xsd/xml.xsd']
        );
        $xsd->setXsd($xsdFile);
        $this->assertEquals($valid, $xsd->isValid($domDocument));
    }

    public function testIsValidWithInvalidXsd(): void
    {
        libxml_clear_errors();
        $domDocument = new DOMDocument();
        $domDocument->loadXML('<test></test>');

        $valid = true;

        vfsStream::setup('root');
        $xsdFile = vfsStream::url('root/xsdfile.xsd');

        $xsd = new Xsd();
        $xsd->setMappings(
            ['http://www.w3.org/2001/XMLSchema' => __DIR__ . '/../../../../data/xsd/xml.xsd']
        );
        $xsd->setXsd($xsdFile);

        $passed = false;
        try {
            $this->assertEquals($valid, $xsd->isValid($domDocument));
        } catch (\RuntimeException $runtimeException) {
            $expectedMessage = 'Failed to load external entity:';
            if (substr($runtimeException->getMessage(), 0, strlen($expectedMessage)) == $expectedMessage) {
                $passed = true;
            }
        }

        $this->assertTrue($passed, 'Expected exception not thrown');
    }

    public function testMaxErrors(): void
    {
        $maxErrors = 123;

        $xsd = new Xsd();
        $xsd->setMaxErrors($maxErrors);
        $this->assertEquals($maxErrors, $xsd->getMaxErrors());
    }

    /**
     * getXmlErrors is there to assist unit testing, have included this here to maintain code coverage
     */
    public function testGetXmlErrors(): void
    {
        libxml_clear_errors();
        $xsd = new Xsd();
        $this->assertEquals([], $xsd->getXmlErrors());
    }
}
