<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\OrganisationBundle as Qry;

/**
 * IrfoOpName
 */
class IrfoOpName extends SingleValueAbstract
{
    public const FIELD  = 'name';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'organisation';
    public const QUERY_CLASS = Qry::class;
}
