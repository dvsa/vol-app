<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Types;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Dvsa\Olcs\Api\Entity\Types\YesNoType;

class YesNoTypeTest extends \PHPUnit\Framework\TestCase
{
    private $type;

    protected function setUp(): void
    {
        if (!YesNoType::hasType('yesno')) {
            YesNoType::addType('yesno', YesNoType::class);
        }
        $this->type = YesNoType::getType('yesno');
    }

    /**
     * test getSqlDeclaration
     */
    public function testGetSqlDeclaration(): void
    {
        $mockPlatform = $this->createStub(MySQLPlatform::class);

        $this->assertEquals(
            'tinyint(1) NOT NULL COMMENT \'(DC2Type:yesno)\'',
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
            [0, 'N']
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
            ['no', 0]
        ];
    }

    /**
     * test getName
     */
    public function testGetName(): void
    {
        $this->assertEquals('yesno', $this->type->getName());
    }
}
