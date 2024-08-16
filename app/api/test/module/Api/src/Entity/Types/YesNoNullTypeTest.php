<?php

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
    public function testGetSqlDeclaration()
    {
        $mockPlatform = $this->createMock(\Doctrine\DBAL\Platforms\MySQLPlatform::class);
        $this->assertEquals(
            'tinyint(1) NULL COMMENT \'(DC2Type:yesnonull)\'',
            $this->type->getSqlDeclaration([], $mockPlatform)
        );
    }

    /**
     * test convertToPHPValue
     *
     * @dataProvider providerConvertToPHPValue
     */
    public function testConvertToPhpValue($input, $output)
    {
        $mockPlatform = $this->createMock(MySQLPlatform::class);
        $this->assertEquals($output, $this->type->convertToPHPValue($input, $mockPlatform));
    }

    /**
     * Provider for convertToPHPValue
     */
    public function providerConvertToPhpValue()
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
     *
     * @dataProvider providerConvertToDatabaseValue
     */
    public function testConvertToDatabaseValue($input, $output)
    {
        $mockPlatform = $this->createMock(MySQLPlatform::class);
        $this->assertEquals($output, $this->type->convertToDatabaseValue($input, $mockPlatform));
    }

    /**
     * Provider for convertToDatabaseValue
     */
    public function providerConvertToDatabaseValue()
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
    public function testGetName()
    {
        $this->assertEquals('yesnonull', $this->type->getName());
    }
}
