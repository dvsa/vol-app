<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Types;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Dvsa\Olcs\Api\Entity\Types\YesNoNullType;

final class YesNoNullTypeTest extends \PHPUnit\Framework\TestCase
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
    public static function providerConvertToPhpValue(): \Iterator
    {
        yield [true, 'Y'];
        yield [false, 'N'];
        yield [1, 'Y'];
        yield [0, 'N'];
        yield [null, null];
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
    public static function providerConvertToDatabaseValue(): \Iterator
    {
        yield ['y', 1];
        yield ['Y', 1];
        yield ['Yes', 1];
        yield ['YES', 1];
        yield ['yes', 1];
        yield ['n', 0];
        yield ['N', 0];
        yield ['No', 0];
        yield ['NO', 0];
        yield ['no', 0];
        yield [null, null];
    }

    /**
     * test getName
     */
    public function testGetName(): void
    {
        $this->assertEquals('yesnonull', $this->type->getName());
    }
}
