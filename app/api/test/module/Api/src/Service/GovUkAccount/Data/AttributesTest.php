<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\GovUkAccount\Data;

use Dvsa\Olcs\Api\Service\GovUkAccount\Data\Attributes;

/**
 * Based on the GDS Verify original, now being used for GovUk Account
 */
class AttributesTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetFullName')]
    public function testGetFullName(array $attributes, string $expected): void
    {
        $attributes = new Attributes($attributes);
        $this->assertSame($expected, $attributes->getFullName());
    }

    public static function dpGetFullName(): array
    {
        return [
            [
                [Attributes::FIRST_NAME => 'John', Attributes::SURNAME => 'Smith'],
                'John Smith'
            ],
            [
                [Attributes::FIRST_NAME => 'John', Attributes::SURNAME => 'Smith'],
                'John Smith'
            ],
            [
                [Attributes::FIRST_NAME => 'John'],
                'John'
            ],
            [
                [Attributes::SURNAME => 'Smith'],
                'Smith'
            ],
            [
                [],
                ''
            ],
        ];
    }

    public function testGetDateOfBirth(): void
    {
        $attributes = new Attributes([Attributes::DATE_OF_BIRTH => '1992-02-28']);
        $this->assertEquals(new \DateTime('1992-02-28'), $attributes->getDateOfBirth());
    }

    public function testGetDateOfBirthEmpty(): void
    {
        $attributes = new Attributes([]);
        $this->assertFalse($attributes->getDateOfBirth());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValidSignature')]
    public function testIsValidSignature(array $attributes, bool $expected): void
    {
        $attributes = new Attributes($attributes);
        $this->assertSame($expected, $attributes->isValidSignature());
    }

    public static function dpIsValidSignature(): array
    {
        return [
            [
                [
                    Attributes::FIRST_NAME => 'John',
                    Attributes::SURNAME => 'Smith',
                    Attributes::DATE_OF_BIRTH => '1999-10-10'],
                true
            ],
            [
                [
                    Attributes::FIRST_NAME => 'John',
                    Attributes::SURNAME => 'Smith',
                    Attributes::DATE_OF_BIRTH => '1999-10-10'
                ],
                true
            ],
            [
                [Attributes::SURNAME => 'Smith', Attributes::DATE_OF_BIRTH => '1999-10-10'],
                false
            ],
            [
                [Attributes::FIRST_NAME => 'John', Attributes::DATE_OF_BIRTH => '1999-10-10'],
                false
            ],
            [
                [Attributes::FIRST_NAME => 'John', Attributes::SURNAME => 'Smith'],
                false
            ],
            [
                [],
                false
            ],
        ];
    }
}
