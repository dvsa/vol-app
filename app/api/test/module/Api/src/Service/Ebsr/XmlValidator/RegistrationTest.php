<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\XmlValidator;

use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\Registration;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class RegistrationTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\XmlValidator
 */
class RegistrationTest extends TestCase
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

        $sut = new Registration();

        $this->assertEquals($valid, $sut->isValid($dom));
    }

    public static function isValidProvider(): array
    {
        return [
            ['<Registrations></Registrations>', false],
            [
                '<Registrations><Registration></Registration><Registration></Registration></Registrations>',
                false
            ],
            ['<Registrations><Registration></Registration></Registrations>', true]
        ];
    }
}
