<?php

namespace Permits\Form\Validator;

use Zend\Validator\Between;

class CustomBetween extends Between
{
    const  TOO_LARGE = "tooLarge";
    const  TOO_SMALL = "tooSmall";

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::TOO_SMALL      => "You must enter a number which can be '%min%' or more.",
        self::TOO_LARGE      => "You must enter a number which is bellow '%max%'"
    );

    /**
     * Custom constructor made to retrieve the TOO_SMALL
     * and TOO_LARGE error messages from the options
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        if (!array_key_exists('too_small_message', $options) || !array_key_exists('too_large_message', $options))
        {
            throw new Exception\InvalidArgumentException("Missing option. 'too_small_message' and 'too_large_message' have to be given");
        }

        $this->messageTemplates[self::TOO_SMALL] = $options['too_small_message'];
        $this->messageTemplates[self::TOO_LARGE] = $options['too_large_message'];

        parent::__construct($options);
    }


    public function isValid($value)
    {
        $this->setValue($value);

        if($this->getMin() > $value || !ctype_digit($myString)) {
            $this->error( self::TOO_SMALL );
            return false;
        }

        if($value > $this->getMax()) {
            $this->error( self::TOO_LARGE);
            return false;
        }

        return true;
    }
}
