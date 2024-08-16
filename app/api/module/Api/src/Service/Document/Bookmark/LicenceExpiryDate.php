<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Licence Expiry Date bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceExpiryDate extends SingleValueAbstract
{
    public const FORMATTER = 'Date';
    public const SRCH_VAL_KEY = 'licence';
    public const FIELD = 'expiryDate';
    public const QUERY_CLASS = Qry::class;
}
