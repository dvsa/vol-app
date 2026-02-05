<?php

declare(strict_types=1);

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
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid(mixed $xml, mixed $valid): void
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $sut = new ServiceClassification();

        $this->assertEquals($valid, $sut->isValid($dom));
    }

    public static function isValidProvider(): array
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
