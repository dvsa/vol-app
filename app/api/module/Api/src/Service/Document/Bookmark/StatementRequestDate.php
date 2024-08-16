<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * StatementRequestDate
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class StatementRequestDate extends StatementFlatAbstract
{
    public const FORMATTER = 'Date';
    public const FIELD  = 'requestedDate';
    public const SRCH_VAL_KEY = 'statement';
}
