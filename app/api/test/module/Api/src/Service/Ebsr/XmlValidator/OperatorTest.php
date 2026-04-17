<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\XmlValidator;

use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\Operator;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class OperatorTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\XmlValidator
 */
class OperatorTest extends TestCase
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

        $sut = new Operator();

        $this->assertEquals($valid, $sut->isValid($dom));
    }

    public static function isValidProvider(): array
    {
        return [
            ['<Operators></Operators>', false],
            [
                '<Operators><LicensedOperator></LicensedOperator><LicensedOperator></LicensedOperator></Operators>',
                false
            ],
            ['<LicensedOperators><LicensedOperator></LicensedOperator></LicensedOperators>', true]
        ];
    }
}
