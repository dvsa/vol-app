<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Licence Trailer Limit Bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceTrailerLimit extends SingleValueAbstract
{
    public const FORMATTER = null;
    public const SRCH_VAL_KEY = 'licence';
    public const FIELD = 'totAuthTrailers';
    public const QUERY_CLASS = Qry::class;
}
