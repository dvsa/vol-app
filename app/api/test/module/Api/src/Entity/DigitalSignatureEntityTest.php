<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity;

use Dvsa\Olcs\Api\Service\GovUkAccount\Data\Attributes;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\DigitalSignature as Entity;
use Mockery as m;

/**
 * DigitalSignature Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class DigitalSignatureEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testAddSignatureInfo(): void
    {
        $sut = new Entity();

        $response = 'response';
        $array = ['attributes'];

        $attributes = m::mock(Attributes::class);
        $attributes->expects('isValidSignature')->andReturnTrue();
        $attributes->expects('getArrayCopy')->withNoArgs()->andReturn($array);

        $sut->addSignatureInfo($attributes, $response);
        $this->assertEquals($array, $sut->getAttributesArray());
        $this->assertEquals($response, $sut->getSamlResponse());
    }

    public function testAddSignatureWithInvalidSignature(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Entity::ERR_INVALID_SIG);

        $sut = new Entity();

        $attributes = m::mock(Attributes::class);
        $attributes->expects('isValidSignature')->andReturnFalse();

        $sut->addSignatureInfo($attributes, 'response');
    }

    public function testGetSetAttributes(): void
    {
        $sut = new Entity();
        $this->assertSame([], $sut->getAttributesArray());
        $sut->setAttributesArray(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $sut->getAttributesArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetSignatureName')]
    public function testGetSignatureName(string $expected, array $attributes): void
    {
        $sut = new Entity();
        $sut->setAttributesArray($attributes);
        $this->assertSame($expected, $sut->getSignatureName());
    }

    public static function dpTestGetSignatureName(): array
    {
        return [
            [
                'Bob Smith',
                [
                    Attributes::FIRST_NAME => 'BOB',
                    Attributes::SURNAME => 'SMITH',
                ],
            ],
            [
                'Bob Smith',
                [
                    Attributes::FIRST_NAME => 'bob',
                    Attributes::SURNAME => 'smith',
                ],
            ],
            [
                'Bob Smith',
                [
                    Attributes::FIRST_NAME => 'bob',
                    Attributes::SURNAME => 'smith',
                ],
            ],
            [
                'Smith',
                [
                    Attributes::FIRST_NAME => '',
                    Attributes::SURNAME => 'smith',
                ],
            ],
            [
                'Bob',
                [
                    Attributes::FIRST_NAME => 'bob',
                    Attributes::SURNAME => '',
                ],
            ],
            [
                '',
                [],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetDateOfBirth')]
    public function testGetDateOfBirth(?string $expected, array $attributes): void
    {
        $sut = new Entity();
        $sut->setAttributesArray($attributes);
        $this->assertSame($expected, $sut->getDateOfBirth());
    }

    public static function dpTestGetDateOfBirth(): array
    {
        return [
            [null, []],
            ['1942-12-12', [Attributes::DATE_OF_BIRTH => '1942-12-12']],
        ];
    }
}
