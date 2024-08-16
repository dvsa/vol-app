<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\FeeBundle as Qry;

/**
 * Goods licence fee amount bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class GvLicFee2 extends SingleValueAbstract
{
    public const SRCH_VAL_KEY = 'fee';
    public const FIELD = 'amount';
    public const QUERY_CLASS = Qry::class;
}
