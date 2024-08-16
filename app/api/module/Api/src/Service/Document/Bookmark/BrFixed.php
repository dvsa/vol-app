<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusFeeTypeBundle as Qry;

/**
 * Class BrFixed (bus reg fee amount)
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrFixed extends SingleValueAbstract
{
    public const FIELD = 'fixedValue';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'busRegId';
    public const QUERY_CLASS = Qry::class;
}
