<?php

/**
 * Create Financial Standing Rate
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Command\System;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/financial-standing-rate")
 * @Transfer\Method("POST")
 */
final class CreateFinancialStandingRate extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"lcat_gv", "lcat_psv"}})
     * @Transfer\Optional
     */
    protected $goodsOrPsv;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"ltyp_r","ltyp_sn","ltyp_si","ltyp_sr"}})
     * @Transfer\Optional
     */
    protected $licenceType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"fin_sta_veh_typ_na","fin_sta_veh_typ_hgv","fin_sta_veh_typ_lgv"}})
     * @Transfer\Optional
     */
    protected $vehicleType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Money")
     */
    protected $firstVehicleRate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Money")
     */
    protected $additionalVehicleRate;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $effectiveFrom;

    /**
     * Gets the value of goodsOrPsv.
     *
     * @return mixed
     */
    public function getGoodsOrPsv()
    {
        return $this->goodsOrPsv;
    }

    /**
     * Gets the value of licenceType.
     *
     * @return mixed
     */
    public function getLicenceType()
    {
        return $this->licenceType;
    }

    /**
     * Gets the value of vehicleType.
     *
     * @return mixed
     */
    public function getVehicleType()
    {
        return $this->vehicleType;
    }

    /**
     * Gets the value of firstVehicleRate.
     *
     * @return mixed
     */
    public function getFirstVehicleRate()
    {
        return $this->firstVehicleRate;
    }

    /**
     * Gets the value of additionalVehicleRate.
     *
     * @return mixed
     */
    public function getAdditionalVehicleRate()
    {
        return $this->additionalVehicleRate;
    }

    /**
     * Gets the value of effectiveFrom.
     *
     * @return mixed
     */
    public function getEffectiveFrom()
    {
        return $this->effectiveFrom;
    }
}
