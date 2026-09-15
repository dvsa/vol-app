<?php

/**
 * Date
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Validator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;

/**
 * Date
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Date extends AbstractValidator
{
    public const DATE_ERR_CONTAINS_STRING = 'DATE_ERR_CONTAINS_STRING';

    public const DATE_ERR_YEAR_LENGTH = 'DATE_ERR_YEAR_LENGTH';

    protected $messageTemplates = [
        self::DATE_ERR_CONTAINS_STRING => self::DATE_ERR_CONTAINS_STRING,
        self::DATE_ERR_YEAR_LENGTH => self::DATE_ERR_YEAR_LENGTH
    ];

    #[\Override]
    public function isValid($value, $context = null)
    {
        if (empty($value)) {
            return true;
        }

        // If it's a Date Time Select We don't care about the time part, so just grab the date
        $date = explode(' ', $value)[0];

        [$year, $month, $day] = explode('-', $date);

        $errors = [];

        if (!is_numeric($year) || !is_numeric($month) || !is_numeric($day)) {
            $errors[] = self::DATE_ERR_CONTAINS_STRING;
        }

        if ((int)$year < 1000) {
            $errors[] = self::DATE_ERR_YEAR_LENGTH;
        }

        if ($errors === []) {
            return true;
        }

        foreach ($errors as $error) {
            $this->error($error);
        }

        return false;
    }
}
