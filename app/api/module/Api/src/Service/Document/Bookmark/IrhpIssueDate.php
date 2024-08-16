<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrhpPermitBundle as Qry;

/**
 * IrhpIssueDate
 *
 * @author Henry White <henry.white@capgemini.com>
 */
class IrhpIssueDate extends SingleValueAbstract
{
    public const FORMATTER = 'DateDayMonthYear';
    public const FIELD  = 'issueDate';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'irhpPermit';
    public const QUERY_CLASS = Qry::class;
}
