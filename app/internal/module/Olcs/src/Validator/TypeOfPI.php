<?php

namespace Olcs\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Class TypeOfPI
 * @package Olcs\Validator
 */
class TypeOfPI extends AbstractValidator
{
    const TM_ONLY   = 'tmOnly';

    /**
     * Digits filter used for validation
     *
     * @var \Zend\Filter\Digits
     */
    protected static $filter = null;

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::TM_ONLY      => "Invalid types selected. 'Transport Manager only' must be the only option selected",
    );

    /**
     * Returns true if type of pi is a selection of options OR transport manager only
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);

        if (in_array('pi_t_tm_only', $this->getValue()) && count($this->getValue()) > 1) {
            $this->error(self::TM_ONLY);
            return false;
        }

        return true;
    }
}
