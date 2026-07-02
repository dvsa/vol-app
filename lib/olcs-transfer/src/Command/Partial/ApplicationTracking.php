<?php

namespace Dvsa\Olcs\Transfer\Command\Partial;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Application Tracking Partial
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationTracking extends AbstractCommand
{
    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $id;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $version;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $addressesStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $businessDetailsStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $businessTypeStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $communityLicencesStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $conditionsUndertakingsStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $convictionsPenaltiesStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $discsStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $financialEvidenceStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $financialHistoryStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $licenceHistoryStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $operatingCentresStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $peopleStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $safetyStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $taxiPhvStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $transportManagersStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $typeOfLicenceStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $declarationsInternalStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $vehiclesSizeStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $psvOperateSmallStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $psvOperateLargeStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $psvSmallConditionsStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $psvOperateNoveltyStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $psvSmallPartWrittenStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $psvDocumentaryEvidenceSmallStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $psvDocumentaryEvidenceLargeStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $psvMainOccupationUndertakingsStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $vehiclesPsvStatus;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0", "1", "2", "3"}})
     */
    protected $vehiclesStatus;



    /**
     * Gets the value of id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the value of version.
     *
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Gets the value of addressesStatus.
     *
     * @return mixed
     */
    public function getAddressesStatus()
    {
        return $this->addressesStatus;
    }

    /**
     * Gets the value of businessDetailsStatus.
     *
     * @return mixed
     */
    public function getBusinessDetailsStatus()
    {
        return $this->businessDetailsStatus;
    }

    /**
     * Gets the value of businessTypeStatus.
     *
     * @return mixed
     */
    public function getBusinessTypeStatus()
    {
        return $this->businessTypeStatus;
    }

    /**
     * Gets the value of communityLicencesStatus.
     *
     * @return mixed
     */
    public function getCommunityLicencesStatus()
    {
        return $this->communityLicencesStatus;
    }

    /**
     * Gets the value of conditionsUndertakingsStatus.
     *
     * @return mixed
     */
    public function getConditionsUndertakingsStatus()
    {
        return $this->conditionsUndertakingsStatus;
    }

    /**
     * Gets the value of convictionsPenaltiesStatus.
     *
     * @return mixed
     */
    public function getConvictionsPenaltiesStatus()
    {
        return $this->convictionsPenaltiesStatus;
    }

    /**
     * Gets the value of discsStatus.
     *
     * @return mixed
     */
    public function getDiscsStatus()
    {
        return $this->discsStatus;
    }

    /**
     * Gets the value of financialEvidenceStatus.
     *
     * @return mixed
     */
    public function getFinancialEvidenceStatus()
    {
        return $this->financialEvidenceStatus;
    }

    /**
     * Gets the value of financialHistoryStatus.
     *
     * @return mixed
     */
    public function getFinancialHistoryStatus()
    {
        return $this->financialHistoryStatus;
    }

    /**
     * Gets the value of licenceHistoryStatus.
     *
     * @return mixed
     */
    public function getLicenceHistoryStatus()
    {
        return $this->licenceHistoryStatus;
    }

    /**
     * Gets the value of operatingCentresStatus.
     *
     * @return mixed
     */
    public function getOperatingCentresStatus()
    {
        return $this->operatingCentresStatus;
    }

    /**
     * Gets the value of peopleStatus.
     *
     * @return mixed
     */
    public function getPeopleStatus()
    {
        return $this->peopleStatus;
    }

    /**
     * Gets the value of safetyStatus.
     *
     * @return mixed
     */
    public function getSafetyStatus()
    {
        return $this->safetyStatus;
    }

    /**
     * Gets the value of taxiPhvStatus.
     *
     * @return mixed
     */
    public function getTaxiPhvStatus()
    {
        return $this->taxiPhvStatus;
    }

    /**
     * Gets the value of transportManagersStatus.
     *
     * @return mixed
     */
    public function getTransportManagersStatus()
    {
        return $this->transportManagersStatus;
    }

    /**
     * Gets the value of typeOfLicenceStatus.
     *
     * @return mixed
     */
    public function getTypeOfLicenceStatus()
    {
        return $this->typeOfLicenceStatus;
    }

    /**
     * Gets the value of declarationsInternalStatus.
     *
     * @return mixed
     */
    public function getDeclarationsInternalStatus()
    {
        return $this->declarationsInternalStatus;
    }

    public function getVehiclesSizeStatus(): ?string
    {
        return $this->vehiclesSizeStatus;
    }

    public function getPsvOperateSmallStatus(): ?string
    {
        return $this->psvOperateSmallStatus;
    }

    public function getPsvOperateLargeStatus(): ?string
    {
        return $this->psvOperateLargeStatus;
    }

    public function getPsvSmallConditionsStatus(): ?string
    {
        return $this->psvSmallConditionsStatus;
    }

    public function getPsvOperateNoveltyStatus(): ?string
    {
        return $this->psvOperateNoveltyStatus;
    }

    public function getPsvSmallPartWrittenStatus(): ?string
    {
        return $this->psvSmallPartWrittenStatus;
    }

    public function getPsvDocumentaryEvidenceSmallStatus(): ?string
    {
        return $this->psvDocumentaryEvidenceSmallStatus;
    }

    public function getPsvDocumentaryEvidenceLargeStatus(): ?string
    {
        return $this->psvDocumentaryEvidenceLargeStatus;
    }

    public function getPsvMainOccupationUndertakingsStatus(): ?string
    {
        return $this->psvMainOccupationUndertakingsStatus;
    }

    /**
     * Gets the value of vehiclesPsvStatus.
     *
     * @return mixed
     */
    public function getVehiclesPsvStatus()
    {
        return $this->vehiclesPsvStatus;
    }

    /**
     * Gets the value of vehiclesStatus.
     *
     * @return mixed
     */
    public function getVehiclesStatus()
    {
        return $this->vehiclesStatus;
    }
}
