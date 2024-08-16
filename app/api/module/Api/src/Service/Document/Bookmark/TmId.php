<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\TransportManagerBundle as Qry;

/**
 * Transport manager id bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmId extends SingleValueAbstract
{
    public const SRCH_VAL_KEY = 'transportManager';
    public const FIELD = 'id';
    public const QUERY_CLASS = Qry::class;
}
