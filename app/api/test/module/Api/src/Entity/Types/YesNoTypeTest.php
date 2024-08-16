<?php

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
    public function testGetSqlDeclaration()
    {
        $mockPlatform = $this->createMock(MySQLPlatform::class);

        $this->assertEquals(
            'tinyint(1) NOT NULL COMMENT \'(DC2Type:yesno)\'',
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
            [0, 'N']
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
            ['no', 0]
        ];
    }

    /**
     * test getName
     */
    public function testGetName()
    {
        $this->assertEquals('yesno', $this->type->getName());
    }
}
