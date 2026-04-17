<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrhpPermitBundle as Qry;

/**
 * IrhpExpiryDate
 *
 * @author Henry White <henry.white@capgemini.com>
 */
class IrhpExpiryDate extends SingleValueAbstract
{
    public const FORMATTER = 'DateDayMonthYear';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'irhpPermit';
    public const QUERY_CLASS = Qry::class;
    public const BUNDLE = [
        'irhpPermitRange' => [
            'irhpPermitStock' => [
                'validTo'
            ],
        ],
    ];

    /**
     * get value
     *
     * @return string
     */
    #[\Override]
    protected function getValue()
    {
        return $this->data['expiryDate'] ?? $this->data['irhpPermitRange']['irhpPermitStock']['validTo'] ?? null;
    }
}
