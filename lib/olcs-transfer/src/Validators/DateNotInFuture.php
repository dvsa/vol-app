<?php

/**
 * Checks a date is not in the future
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\AbstractValidator as AbstractValidator;

/**
 * Checks a date is not in the future
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DateNotInFuture extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    public const IN_FUTURE = 'inFuture';

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::IN_FUTURE => "This date is not allowed to be in the future",
    ];

    /**
     * Returns true if the date is not in the future
     *
     * @param  mixed $value
     * @return bool
     */
    #[\Override]
    public function isValid($value)
    {
        $date = new \DateTime($value);
        $today = $this->getNowDateTime();

        if ($date > $today) {
            $this->error(self::IN_FUTURE);
            return false;
        }

        return true;
    }

    /**
     * Get Now
     *
     * @return \DateTime
     */
    protected function getNowDateTime()
    {
        return new \DateTime();
    }
}
