<?php

namespace Common\Form\Elements\Validators;

use Laminas\Validator\AbstractValidator;
use Traversable;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\Date as DateValidator;

/**
 * Checks that if a time is entered then the corresponding date is also set
 * (Used on Impoundings)
 */
class DateWithTime extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    public const MISSING_TIME = 'missingTime';

    public const MISSING_TOKEN = 'missingToken';

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::MISSING_TIME => "Hearing date requires a valid hearing time",
        self::MISSING_TOKEN => 'No token was provided to match against',
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'token' => 'tokenString'
    ];

    /**
     * Original token against which to validate
     * @var string
     */
    protected $tokenString;

    protected $token;

    /**
     * Sets validator options
     *
     * @param  mixed $token
     */
    public function __construct($token = null)
    {
        if ($token instanceof Traversable) {
            $token = ArrayUtils::iteratorToArray($token);
        }

        if (is_array($token) && array_key_exists('token', $token)) {
            $this->setToken($token['token']);
        } elseif (null !== $token) {
            $this->setToken($token);
        }

        parent::__construct(is_array($token) ? $token : null);
    }

    /**
     * Retrieve token
     *
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token against which to compare
     *
     * @return DateWithTime
     */
    public function setToken(mixed $token)
    {
        $this->tokenString = (is_array($token) ? var_export($token, true) : (string) $token);
        $this->token = $token;
        return $this;
    }

    /**
     * Checks whether the corresponding date field contains a valid date
     *
     * @return bool
     */
    #[\Override]
    public function isValid($value, array $context = null)
    {
        unset($value);

        $time = $context[$this->getToken()];

        $date = new DateValidator(['format' => 'H:i']);

        if (!$date->isValid($time)) {
            $this->error(self::MISSING_TIME);
            return false;
        }

        return true;
    }
}
