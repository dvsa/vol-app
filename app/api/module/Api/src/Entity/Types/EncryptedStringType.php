<?php

namespace Dvsa\Olcs\Api\Entity\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use phpseclib\Crypt;
use phpseclib\Crypt\Base;

/**
 * Class EncryptedStringType
 */
class EncryptedStringType extends StringType
{
    public const TYPE = 'encrypted_string';

    /**
     * @var Crypt\Base
     */
    private $encrypter;

    /**
     * Get the name of this type
     *
     * @return string
     */
    public function getName()
    {
        return self::TYPE;
    }

    /**
     * Convert value to PHP value
     *
     * @param string           $value    Value from DB
     * @param AbstractPlatform $platform Value for PHP
     *
     * @return bool|string
     */
    public function convertToPhpValue($value, AbstractPlatform $platform)
    {
        return $this->getEncrypter()->decrypt(base64_decode($value));
    }

    /**
     * Convert value to DB value
     *
     * @param string           $value    Value from PHP
     * @param AbstractPlatform $platform Value to DB
     *
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return base64_encode($this->getEncrypter()->encrypt($value));
    }

    /**
     * Set the Encrypter to use
     *
     * @param Base|null $ciper Cipher to use for encryption
     *
     * @return void
     */
    public function setEncrypter(?Base $ciper)
    {
        $this->encrypter = $ciper;
    }

    /**
     * Get the Encrypter
     *
     * @return Crypt\Base
     */
    public function getEncrypter()
    {
        if ($this->encrypter === null) {
            throw new \RuntimeException('An encrypter must be set to allow encrypting data');
        }

        return $this->encrypter;
    }
}
