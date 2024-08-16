<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Licence - Review Date
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ReviewDate extends SingleValueAbstract
{
    public const FORMATTER = 'Date';
    public const SRCH_VAL_KEY = 'licence';
    public const FIELD = 'reviewDate';
    public const QUERY_CLASS = Qry::class;
}
