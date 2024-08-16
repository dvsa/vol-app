<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Licence - In Force Date
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class IssueDate extends SingleValueAbstract
{
    public const FORMATTER = 'Date';
    public const SRCH_VAL_KEY = 'licence';
    public const FIELD = 'inForceDate';
    public const QUERY_CLASS = Qry::class;
}
