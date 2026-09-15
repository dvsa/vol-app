<?php

namespace Dvsa\Olcs\Transfer\Command\TransportManagerApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @Transfer\RouteName("backend/tm-responsibilities/transport-manager-application/single")
 * @Transfer\Method("PUT")
 */
final class UpdateForResponsibilities extends AbstractCommand
{
    use Traits\Identity;
    use Traits\HasUndertakenTraining;
    use Traits\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"tm_t_i","tm_t_e"}})
     * @Transfer\Optional
     */
    protected $tmType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isOwner;

    /**
     * @Transfer\Filter("Laminas\I18n\Filter\NumberFormat")
     * @Transfer\Validator("Laminas\I18n\Validator\IsFloat")
     * @Transfer\Optional
     */
    protected $hoursMon;

    /**
     * @Transfer\Filter("Laminas\I18n\Filter\NumberFormat")
     * @Transfer\Validator("Laminas\I18n\Validator\IsFloat")
     * @Transfer\Optional
     */
    protected $hoursTue;

    /**
     * @Transfer\Filter("Laminas\I18n\Filter\NumberFormat")
     * @Transfer\Validator("Laminas\I18n\Validator\IsFloat")
     * @Transfer\Optional
     */
    protected $hoursWed;

    /**
     * @Transfer\Filter("Laminas\I18n\Filter\NumberFormat")
     * @Transfer\Validator("Laminas\I18n\Validator\IsFloat")
     * @Transfer\Optional
     */
    protected $hoursThu;

    /**
     * @Transfer\Filter("Laminas\I18n\Filter\NumberFormat")
     * @Transfer\Validator("Laminas\I18n\Validator\IsFloat")
     * @Transfer\Optional
     */
    protected $hoursFri;

    /**
     * @Transfer\Filter("Laminas\I18n\Filter\NumberFormat")
     * @Transfer\Validator("Laminas\I18n\Validator\IsFloat")
     * @Transfer\Optional
     */
    protected $hoursSat;

    /**
     * @Transfer\Filter("Laminas\I18n\Filter\NumberFormat")
     * @Transfer\Validator("Laminas\I18n\Validator\IsFloat")
     * @Transfer\Optional
     */
    protected $hoursSun;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $additionalInformation;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "tmap_st_awaiting_signature", "tmap_st_incomplete", "tmap_st_operator_signed",
     *              "tmap_st_postal_application", "tmap_st_received", "tmap_st_tm_signed"
     *          }
     *      }
     * )
     */
    protected $tmApplicationStatus;

    /**
     * Get Transport Manager Application ID
     *
     * @return string
     */
    public function getTmType()
    {
        return $this->tmType;
    }

    /**
     * Is Owner
     *
     * @return string
     */
    public function getIsOwner()
    {
        return $this->isOwner;
    }

    /**
     * Hours at Monday
     *
     * @return int
     */
    public function getHoursMon()
    {
        return $this->hoursMon;
    }

    /**
     * Hours at Tuesday
     *
     * @return int
     */
    public function getHoursTue()
    {
        return $this->hoursTue;
    }

    /**
     * Hours at Wednesday
     *
     * @return int
     */
    public function getHoursWed()
    {
        return $this->hoursWed;
    }

    /**
     * Hours at Thuesday
     *
     * @return int
     */
    public function getHoursThu()
    {
        return $this->hoursThu;
    }

    /**
     * Hours at Friday
     *
     * @return int
     */
    public function getHoursFri()
    {
        return $this->hoursFri;
    }

    /**
     * Hours at Saturday
     *
     * @return int
     */
    public function getHoursSat()
    {
        return $this->hoursSat;
    }

    /**
     * Hours at Sunday
     *
     * @return int
     */
    public function getHoursSun()
    {
        return $this->hoursSun;
    }

    /**
     * Get additional information
     *
     * @return string
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }

    /**
     * Get Transport manager status
     *
     * @return string
     */
    public function getTmApplicationStatus()
    {
        return $this->tmApplicationStatus;
    }
}
