<?php

namespace Dvsa\Olcs\Api\Entity\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use phpseclib3\Crypt\AES;

class EncryptedStringType extends StringType
{
    public const TYPE = 'encrypted_string';

    private ?AES $encrypter = null;

    /**
     * Get the name of this type
     *
     * @return string
     */
    #[\Override]
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
    #[\Override]
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
    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return base64_encode($this->getEncrypter()->encrypt($value));
    }

    /**
     * Set the Encrypter to use
     */
    public function setEncrypter(?AES $cipher): self
    {
        $this->encrypter = $cipher;

        return $this;
    }

    public function getEncrypter(): AES
    {
        if ($this->encrypter === null) {
            throw new \RuntimeException('An encrypter must be set to allow encrypting data');
        }

        return $this->encrypter;
    }
}
