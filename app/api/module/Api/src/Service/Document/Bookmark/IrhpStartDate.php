<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrhpPermitStockBundle as Qry;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * IrhpStartDate
 *
 * @author Henry White <henry.white@capgemini.com>
 */
class IrhpStartDate extends SingleValueAbstract
{
    public const FORMATTER = 'DateDayMonthYear';
    public const FIELD  = 'validFrom';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'irhpPermitStock';
    public const QUERY_CLASS = Qry::class;

    #[\Override]
    public function render()
    {
        $now = strtotime('today midnight');

        if (isset($this->data['validFrom'])) {
            if ($now > strtotime((string) $this->data['validFrom'])) {
                $this->setData(['validFrom' => date("d F Y", $now)]);
            }
        }

        return parent::render();
    }
}
