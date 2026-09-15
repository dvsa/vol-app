<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\XmlValidator;

use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\ServiceClassification;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class ServiceClassificationTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\XmlValidator
 */
final class ServiceClassificationTest extends TestCase
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

    public static function isValidProvider(): \Iterator
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
        yield ['<Service></Service>', false];
        yield ['<Service><ServiceClassification></ServiceClassification></Service>', true];
        yield [$multiServiceXml, true];
        yield [$multiServiceXmlInvalid, false];
    }
}
