<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Licence holder name bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceNumber extends SingleValueAbstract
{
    public const SRCH_VAL_KEY = 'licence';
    public const FIELD = 'licNo';
    public const QUERY_CLASS = Qry::class;
}
