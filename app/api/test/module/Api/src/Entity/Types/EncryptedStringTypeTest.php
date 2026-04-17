<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Types;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use phpseclib3\Crypt\AES;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Types\EncryptedStringType;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Types\EncryptedStringType::class)]
class EncryptedStringTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EncryptedStringType
     */
    private $sut;

    protected function setUp(): void
    {
        if (!EncryptedStringType::hasType(EncryptedStringType::TYPE)) {
            EncryptedStringType::addType(EncryptedStringType::TYPE, EncryptedStringType::class);
        }
        $this->sut = EncryptedStringType::getType(EncryptedStringType::TYPE);
    }

    public function testGetName(): void
    {
        $this->assertSame(EncryptedStringType::TYPE, $this->sut->getName());
    }

    public function testGetEncrypterNotSet(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->sut->setEncrypter(null);
        $this->sut->getEncrypter();
    }

    public function testSetGetEncrypter(): void
    {
        $blockCipher = m::mock(AES::class);
        $this->sut->setEncrypter($blockCipher);
        $this->assertSame($blockCipher, $this->sut->getEncrypter());
    }

    public function testConvertToPhpValue(): void
    {
        $platform = $this->createStub(MySQLPlatform::class);
        $blockCipher = m::mock(AES::class);
        $blockCipher->shouldReceive('decrypt')->with('ENCRYPTED')->once()->andReturn('DECRYPTED');
        $this->sut->setEncrypter($blockCipher);
        $this->assertSame('DECRYPTED', $this->sut->convertToPHPValue(base64_encode('ENCRYPTED'), $platform));
    }

    public function testConvertToDatabaseValue(): void
    {
        $platform = $this->createStub(MySQLPlatform::class);
        $blockCipher = m::mock(AES::class);
        $blockCipher->shouldReceive('encrypt')->with('DECRYPTED')->once()->andReturn('ENCRYPTED');
        $this->sut->setEncrypter($blockCipher);
        $this->assertSame(base64_encode('ENCRYPTED'), $this->sut->convertToDatabaseValue('DECRYPTED', $platform));
    }
}
