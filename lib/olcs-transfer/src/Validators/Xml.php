<?php

/**
 * XML validator
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\AbstractValidator;
use Laminas\Xml\Security as XmlSecurityValidator;
use Laminas\Xml\Exception\RuntimeException;

/**
 * XML validator
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Xml extends AbstractValidator
{
    public const XML_NOT_VALID = 'xml_not_valid_error';
    public const XML_CONTAINS_EXTERNAL_ENTITIES = 'xml_external_entities_error';

    protected $messageTemplates = [
        self::XML_NOT_VALID => 'The XML was not valid',
        self::XML_CONTAINS_EXTERNAL_ENTITIES => 'Detected use of ENTITY in XML, disabled to prevent XXE/XEE attacks',
    ];

    /**
     * @var XmlSecurityValidator
     */
    protected $securityValidator;

    public function setSecurityValidator(XmlSecurityValidator $securityValidator): void
    {
        $this->securityValidator = $securityValidator;
    }

    public function getSecurityValidator(): XmlSecurityValidator
    {
        if ($this->securityValidator === null) {
            $this->securityValidator = new XmlSecurityValidator();
        }

        return $this->securityValidator;
    }

    /**
     * Returns true if and only if $value is contained in the haystack option. If the strict
     * option is true, then the type of $value is also checked.
     *
     * @param mixed $value
     * @return string|bool
     */
    #[\Override]
    public function isValid($value)
    {
        try {
            $validator = $this->getSecurityValidator();
            $valid = $validator::scan($value, new \DOMDocument('1.0'));
        } catch (RuntimeException) {
            $this->error(self::XML_CONTAINS_EXTERNAL_ENTITIES);
            return false;
        }

        if (!$valid) {
            $this->error(self::XML_NOT_VALID);
            return false;
        }

        return $value;
    }
}
