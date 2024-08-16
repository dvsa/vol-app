<?php

namespace Dvsa\OlcsTest\Api\Service\Ebsr\XmlValidator;

use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\ServiceClassification;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class ServiceClassificationTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\XmlValidator
 */
class ServiceClassificationTest extends TestCase
{
    /**
     * @param $xml
     * @param $valid
     * @dataProvider isValidProvider
     */
    public function testIsValid($xml, $valid)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $sut = new ServiceClassification();

        $this->assertEquals($valid, $sut->isValid($dom));
    }

    public function isValidProvider()
    {
        $multiServiceXml = '
            <Services>
                <Service><ServiceClassification></ServiceClassification></Service>
                <Service><ServiceClassification></ServiceClassification></Service>
            </Services>
        ';

        $multiServiceXmlInvalid = '
            <Services>
                <Service><ServiceClassification></ServiceClassification></Service>
                <Service></Service>
            </Services>
        ';

        return [
            ['<Service></Service>', false],
            ['<Service><ServiceClassification></ServiceClassification></Service>', true],
            [$multiServiceXml, true],
            [$multiServiceXmlInvalid, false]
        ];
    }
}
