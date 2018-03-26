<?php

namespace Olcs\Validator;

use Zend\Validator\AbstractValidator;

class InterimTrailerAuthority extends AbstractValidator
{
    const TRAILER_AUTHORITY_EXCEEDED = "trailerAuthExceeded";

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::TRAILER_AUTHORITY_EXCEEDED => "The interim trailer authority cannot exceed the total trailer authority",
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
        $totalAuthTrailers = ($context['totAuthTrailers'] == null ? 0 : $context['totAuthTrailers']);

        if ($this->getValue() > $totalAuthTrailers) {
            $this->error(self::TRAILER_AUTHORITY_EXCEEDED);
            return false;
        }
        return true;
    }
}
