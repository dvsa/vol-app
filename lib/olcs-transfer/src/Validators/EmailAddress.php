<?php

/**
 * Custom email validator
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\Hostname;
use Laminas\Validator\AbstractValidator;

/**
 * Custom email validator
 *
 * @NOTE Mostly moved from Laminas validator, however re-formatted to present friendlier errors
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EmailAddress extends AbstractValidator
{
    /**
     * Should be consistent across the application, see OLCS-9884
     */
    public const EMAIL_ADDRESS_MAX_LENGTH = 254;
    public const INVALID            = 'emailAddressInvalid';
    public const INVALID_FORMAT     = 'emailAddressInvalidFormat';
    public const ERROR_INVALID      = 'emailAddressLengthNotInRange';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID        => 'Invalid type given. String expected',
        self::INVALID_FORMAT => 'The input is not a valid email address.',
        self::ERROR_INVALID  => 'email-validator.error-message'
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'hostname'  => 'hostname',
        'localPart' => 'localPart'
    ];

    /**
     * Array of valid top-level-domains in addition to those used in Laminas hostname validator
     *
     * @var array
     */
    protected $additionalValidTlds = [
        'ltd'
    ];

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $localPart;

    /**
     * Internal options array
     */
    protected $options = [
        'useDomainCheck'    => true,
        'allow'             => Hostname::ALLOW_DNS,
        'hostnameValidator' => null,
    ];

    /**
     * Instantiates hostname validator for local use
     *
     * The following additional option keys are supported:
     * 'hostnameValidator' => A hostname validator, see Laminas\Validator\Hostname
     * 'allow'             => Options for the hostname validator, see Laminas\Validator\Hostname::ALLOW_*
     *
     * @param array|\Traversable $options OPTIONAL
     */
    public function __construct($options = [])
    {
        if (!is_array($options)) {
            $options = func_get_args();
            $temp['allow'] = array_shift($options);
            if (!empty($options)) {
                $temp['useMxCheck'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['hostnameValidator'] = array_shift($options);
            }

            $options = $temp;
        }

        parent::__construct($options);
    }

    /**
     * Sets the validation failure message template for a particular key
     * Adds the ability to set messages to the attached hostname validator
     *
     * @param  string $messageString
     * @param  string $messageKey     OPTIONAL
     * @return static Provides a fluent interface
     */
    #[\Override]
    public function setMessage($messageString, $messageKey = null): AbstractValidator
    {
        if ($messageKey === null) {
            $this->getHostnameValidator()->setMessage($messageString);
            parent::setMessage($messageString);
            return $this;
        }

        if (!isset($this->messageTemplates[$messageKey])) {
            $this->getHostnameValidator()->setMessage($messageString, $messageKey);
        } else {
            parent::setMessage($messageString, $messageKey);
        }

        return $this;
    }

    /**
     * Returns the set hostname validator
     *
     * If was not previously set then lazy load a new one
     *
     * @return Hostname
     */
    public function getHostnameValidator()
    {
        if (!isset($this->options['hostnameValidator'])) {
            /**
             * @psalm-suppress InvalidArgument
             *
             * Docblock types as array, but handles non-array values.
             * */
            $this->options['hostnameValidator'] = new Hostname($this->getAllow());
        }

        return $this->options['hostnameValidator'];
    }

    /**
     * @param Hostname $hostnameValidator OPTIONAL
     * @return EmailAddress Provides a fluent interface
     */
    public function setHostnameValidator(?Hostname $hostnameValidator = null)
    {
        $this->options['hostnameValidator'] = $hostnameValidator;

        return $this;
    }

    /**
     * Returns the allow option of the attached hostname validator
     *
     * @return int
     */
    public function getAllow()
    {
        return $this->options['allow'];
    }

    /**
     * Sets the allow option of the hostname validator to use
     *
     * @param int $allow
     * @return EmailAddress Provides a fluent interface
     */
    public function setAllow($allow)
    {
        $this->options['allow'] = $allow;
        if (isset($this->options['hostnameValidator'])) {
            $this->options['hostnameValidator']->setAllow($allow);
        }

        return $this;
    }

    /**
     * Returns the set domainCheck option
     *
     * @return bool
     */
    public function getDomainCheck()
    {
        return $this->options['useDomainCheck'];
    }

    /**
     * Sets if the domain should also be checked
     * or only the local part of the email address
     *
     * @param  bool $domain
     * @return EmailAddress Fluid Interface
     */
    public function useDomainCheck($domain = true)
    {
        $this->options['useDomainCheck'] = (bool) $domain;
        return $this;
    }

    /**
     * Internal method to validate the local part of the email address
     *
     * @return bool
     */
    protected function validateLocalPart()
    {
        // Dot-atom characters are: 1*atext *("." 1*atext)
        // atext: ALPHA / DIGIT / and "!", "#", "$", "%", "&", "'", "*",
        //        "+", "-", "/", "=", "?", "^", "_", "`", "{", "|", "}", "~"
        $atext = 'a-zA-Z0-9\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e';
        if (preg_match('/^[' . $atext . ']+(\x2e+[' . $atext . ']+)*$/', $this->localPart)) {
            return true;
        }

        // Try quoted string format (RFC 5321 Chapter 4.1.2)

        // Quoted-string characters are: DQUOTE *(qtext/quoted-pair) DQUOTE
        $qtext      = '\x20-\x21\x23-\x5b\x5d-\x7e'; // %d32-33 / %d35-91 / %d93-126
        $quotedPair = '\x20-\x7e'; // %d92 %d32-126
        if (preg_match('/^"([' . $qtext . ']|\x5c[' . $quotedPair . '])*"$/', $this->localPart)) {
            return true;
        }

        return false;
    }

    /**
     * Internal method to validate the hostname part of the email address
     *
     * @return bool
     */
    protected function validateHostnamePart()
    {

        $valid = $this->getHostnameValidator()->setTranslator($this->getTranslator())->isValid($this->hostname);

        if (!$valid) {
            $hostnameValidatorTldDisabled = new Hostname(['allow' => $this->getAllow(), 'useTldCheck' => false]);
            $valid = $hostnameValidatorTldDisabled->setTranslator($this->getTranslator())
                ->isValid($this->hostname) ? $this->isAdditionalValidTld() : false;
        }

        return $valid;
    }

    /**
     * Internal method to validate additional valid top-level-domains not included in Laminas validTlds
     *
     * @return bool
     */
    protected function isAdditionalValidTld()
    {
        $tld = substr($this->hostname, strrpos($this->hostname, '.') + 1);
        return in_array(strtolower($tld), $this->additionalValidTlds);
    }

    /**
     * Splits the given value in hostname and local part of the email address
     *
     * @param string $value Email address to be split
     * @return bool Returns false when the email can not be split
     */
    protected function splitEmailParts($value)
    {
        // Split email address up and disallow '..'
        if (
            (str_contains($value, '..')) or
            (!preg_match('/^(.+)@([^@]+)$/', $value, $matches))
        ) {
            return false;
        }

        $this->localPart = $matches[1];
        $this->hostname  = $matches[2];

        return true;
    }

    /**
     * Defined by Laminas\Validator\ValidatorInterface
     *
     * Returns true if and only if $value is a valid email address
     * according to RFC2822
     *
     * @link   http://www.ietf.org/rfc/rfc2822.txt RFC2822
     * @link   http://www.columbia.edu/kermit/ascii.html US-ASCII characters
     * @param  mixed $value
     * @return bool
     */
    #[\Override]
    public function isValid($value)
    {
        // Check length
        if ($value === null || strlen($value) > self::EMAIL_ADDRESS_MAX_LENGTH) {
            $this->error(self::ERROR_INVALID);
            return false;
        }

        // Check string
        if (!is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        // Split email address up and disallow '..'
        if (!$this->splitEmailParts($value)) {
            $this->error(self::INVALID_FORMAT);
            return false;
        }

        // Match hostname part
        if ($this->options['useDomainCheck']) {
            $hostname = $this->validateHostnamePart();
            if ($hostname === false) {
                $this->error(self::INVALID_FORMAT);
                return false;
            }
        }

        $local = $this->validateLocalPart();

        if ($local === false) {
            $this->error(self::INVALID_FORMAT);
            return false;
        }

        return true;
    }
}
