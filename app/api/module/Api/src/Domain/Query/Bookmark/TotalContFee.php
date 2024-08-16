<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * TotalContFee
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TotalContFee extends AbstractQuery
{
    protected $goodsOrPsv;

    protected $licenceType;

    protected $trafficArea;

    protected $effectiveFrom;

    /**
     * @return mixed
     */
    public function getGoodsOrPsv()
    {
        return $this->goodsOrPsv;
    }

    /**
     * @return mixed
     */
    public function getLicenceType()
    {
        return $this->licenceType;
    }

    /**
     * @return mixed
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * @return mixed
     */
    public function getEffectiveFrom()
    {
        return $this->effectiveFrom;
    }
}
