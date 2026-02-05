<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Types;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Dvsa\Olcs\Api\Entity\Types\YesNoNullType;

class YesNoNullTypeTest extends \PHPUnit\Framework\TestCase
{
    private $type;

    protected function setUp(): void
    {
        if (!YesNoNullType::hasType('yesnonull')) {
            YesNoNullType::addType('yesnonull', YesNoNullType::class);
        }
        $this->type = YesNoNullType::getType('yesnonull');
    }

    /**
     * test getSqlDeclaration
     */
    public function testGetSqlDeclaration(): void
    {
        $mockPlatform = $this->createStub(\Doctrine\DBAL\Platforms\MySQLPlatform::class);
        $this->assertEquals(
            'tinyint(1) NULL COMMENT \'(DC2Type:yesnonull)\'',
            $this->type->getSqlDeclaration([], $mockPlatform)
        );
    }

    /**
     * test convertToPHPValue
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerConvertToPHPValue')]
    public function testConvertToPhpValue(mixed $input, mixed $output): void
    {
        $mockPlatform = $this->createStub(MySQLPlatform::class);
        $this->assertEquals($output, $this->type->convertToPHPValue($input, $mockPlatform));
    }

    /**
     * Provider for convertToPHPValue
     */
    public static function providerConvertToPhpValue(): array
    {
        return [
            [true, 'Y'],
            [false, 'N'],
            [1, 'Y'],
            [0, 'N'],
            [null, null],
        ];
    }

    /**
     * test convertToDatabaseValue
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerConvertToDatabaseValue')]
    public function testConvertToDatabaseValue(mixed $input, mixed $output): void
    {
        $mockPlatform = $this->createStub(MySQLPlatform::class);
        $this->assertEquals($output, $this->type->convertToDatabaseValue($input, $mockPlatform));
    }

    /**
     * Provider for convertToDatabaseValue
     */
    public static function providerConvertToDatabaseValue(): array
    {
        return [
            ['y', 1],
            ['Y', 1],
            ['Yes', 1],
            ['YES', 1],
            ['yes', 1],
            ['n', 0],
            ['N', 0],
            ['No', 0],
            ['NO', 0],
            ['no', 0],
            [null, null],
        ];
    }

    /**
     * test getName
     */
    public function testGetName(): void
    {
        $this->assertEquals('yesnonull', $this->type->getName());
    }
}
