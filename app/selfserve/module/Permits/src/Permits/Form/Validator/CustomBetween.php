<?php

namespace Permits\Form\Validator;

use Zend\Validator\Between;

class CustomBetween extends Between
{
    const  TOO_LARGE = "tooLarge";
    const  TOO_SMALL = "tooSmall";
    const  NOT_DIGIT = "notDigit";

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::TOO_SMALL      => "You must enter a number which can be '%min%' or more.",
        self::TOO_LARGE      => "You must enter a number which is bellow '%max%'",
        self::NOT_DIGIT      => "You must enter a whole number"

    );

    /**
     * Custom constructor made to retrieve the TOO_SMALL
     * and TOO_LARGE error messages from the options
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        if (!array_key_exists('too_small_message', $options) || !array_key_exists('too_large_message', $options) || !array_key_exists('not_digit_message', $options))
        {
            throw new Exception\InvalidArgumentException("Missing option. 'not_digit_message', 'too_small_message' and 'too_large_message' have to be given");
        }

        $this->messageTemplates[self::TOO_SMALL] = $options['too_small_message'];
        $this->messageTemplates[self::TOO_LARGE] = $options['too_large_message'];
        $this->messageTemplates[self::NOT_DIGIT] = $options['not_digit_message'];

        parent::__construct($options);
    }


    public function isValid($value)
    {
        $this->setValue($value);

        if($this->getMin() > $value || $value == '') {
            $this->error( self::TOO_SMALL );
            return false;
        }

        if(!ctype_digit($value)) {
            $this->error( self::NOT_DIGIT );
            return false;
        }

        if($value > $this->getMax()) {
            $this->error( self::TOO_LARGE);
            return false;
        }

        return true;
    }
}
