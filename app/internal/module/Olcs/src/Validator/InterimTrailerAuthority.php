<?php

namespace Olcs\Validator;

use Zend\Validator\AbstractValidator;
use Olcs\Controller\Lva\Traits;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class InterimTrailerAuthority
 *
 * @package Olcs\Validator
 */
class InterimTrailerAuthority extends AbstractValidator
{
    const GREATER_THAN = "overMax";

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::GREATER_THAN => "The trailer authority has exceeded the maximum trailer authority",
    );

    /**
     * Returns true if interim authorised trailers < total authorised trailers
     *
     * @param mixed $value
     * @param null  $context
     *
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        if ($this->getValue() > $context['maxAuthTrailers']) {
            $this->error(self::GREATER_THAN);
            return false;
        }
        return true;
    }
}
