<?php

namespace Dvsa\Olcs\Api\Entity\OtherLicence;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;

/**
 * OtherLicence Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="other_licence",
 *    indexes={
 *        @ORM\Index(name="ix_other_licence_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_other_licence_previous_licence_type", columns={"previous_licence_type"}),
 *        @ORM\Index(name="ix_other_licence_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_other_licence_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_other_licence_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(
 *          name="ix_other_licence_transport_manager_application_id", columns={"transport_manager_application_id"}
 *        ),
 *        @ORM\Index(name="fk_other_licence_transport_manager_licence1_idx", columns={"transport_manager_licence_id"}),
 *        @ORM\Index(name="fk_other_licence_ref_data1_idx", columns={"role"})
 *    }
 * )
 */
class OtherLicence extends AbstractOtherLicence implements \Dvsa\Olcs\Api\Entity\OrganisationProviderInterface
{
    public const TYPE_CURRENT = 'prev_has_licence';
    public const TYPE_APPLIED = 'prev_had_licence';
    public const TYPE_REFUSED = 'prev_been_refused';
    public const TYPE_REVOKED = 'prev_been_revoked';
    public const TYPE_PUBLIC_INQUIRY = 'prev_been_at_pi';
    public const TYPE_DISQUALIFIED = 'prev_been_disqualified_tc';
    public const TYPE_HELD = 'prev_purchased_assets';

    public const ERROR_TYPE_NOT_IMPLEMENTED = 'AP-LH-OL-1';
    public const ERROR_FIELD_IS_REQUIRED = 'AP-LH-OL-2';
    public const ERROR_DATE_IN_FUTURE = 'AP-LH-OL-3';
    public const ERROR_TYPE_IS_EMPTY = 'AP-LH-OL-4';

    protected $requiredFields = [
        self::TYPE_CURRENT => ['licNo', 'holderName', 'willSurrender'],
        self::TYPE_APPLIED => ['licNo', 'holderName'],
        self::TYPE_REFUSED => ['licNo', 'holderName'],
        self::TYPE_REVOKED => ['licNo', 'holderName'],
        self::TYPE_PUBLIC_INQUIRY => ['licNo', 'holderName'],
        self::TYPE_DISQUALIFIED => ['licNo', 'holderName', 'disqualificationDate', 'disqualificationLength'],
        self::TYPE_HELD => ['licNo', 'holderName', 'purchaseDate']
    ];

    /**
     * Update OtherLicence properties
     *
     * @param string $licNo                  Licence number
     * @param string $holderName             Holder name
     * @param string $willSurrender          "Y" or "N"
     * @param string $disqualificationDate   Disqualification date as string, eg '2017-10-25'
     * @param int    $disqualificationLength Disqualification length
     * @param string $purchaseDate           Purchase date as string, eg '2017-10-25'
     *
     * @return bool
     */
    public function updateOtherLicence(
        $licNo,
        $holderName,
        $willSurrender,
        $disqualificationDate,
        $disqualificationLength,
        $purchaseDate
    ) {
        $this->validateOtherLicence(
            $licNo,
            $holderName,
            $willSurrender,
            $disqualificationDate,
            $disqualificationLength,
            $purchaseDate
        );
        $previousLicenceType = $this->getPreviousLicenceType()->getId();
        foreach ($this->requiredFields[$previousLicenceType] as $field) {
            match ($field) {
                'licNo' => $this->setLicNo($licNo),
                'holderName' => $this->setHolderName($holderName),
                'willSurrender' => $this->setWillSurrender($willSurrender),
                'disqualificationDate' => $this->setDisqualificationDate(new \DateTime($disqualificationDate)),
                'disqualificationLength' => $this->setDisqualificationLength($disqualificationLength),
                'purchaseDate' => $this->setPurchaseDate(new \DateTime($purchaseDate)),
                default => throw new \RuntimeException("Unexpected field '{$field}'"),
            };
        }
        return true;
    }

    /**
     * Validate properties to set on an OtherLicence
     *
     * @param string $licNo                  Licence number
     * @param string $holderName             Holder name
     * @param string $willSurrender          "Y" or "N"
     * @param string $disqualificationDate   Disqualification date as string, eg '2017-10-25'
     * @param int    $disqualificationLength Disqualification length
     * @param string $purchaseDate           Purchase date as string, eg '2017-10-25'
     *
     * @return bool Return true if valid, otherwise throws ValidationException
     * @throws ValidationException
     */
    protected function validateOtherLicence(
        $licNo,
        $holderName,
        $willSurrender,
        $disqualificationDate,
        $disqualificationLength,
        $purchaseDate
    ) {
        $errors = [];

        $previousLicenceType = $this->getPreviousLicenceType();

        // need to remove it when other sections with otherLicence will be done
        $this->checkEmptyPreviousLicenceType($previousLicenceType);
        $previousLicenceTypeId = $previousLicenceType->getId();

        switch ($previousLicenceTypeId) {
            case self::TYPE_CURRENT:
                $errors = $this->checkRequiredFields(
                    [$willSurrender, $licNo, $holderName],
                    ['willSurrender', 'licNo', 'holderName'],
                    $errors
                );
                break;
            case self::TYPE_DISQUALIFIED:
                $errors = $this->checkDateNotEmptyAndNotInFuture(
                    $disqualificationDate,
                    'disqualificationDate',
                    $errors
                );
                $errors = $this->checkRequiredFields(
                    [$licNo, $holderName],
                    ['licNo', 'holderName'],
                    $errors
                );
                $errors = $this->checkRequiredField($disqualificationLength, 'disqualificationLength', $errors);

                break;
            case self::TYPE_HELD:
                $errors = $this->checkDateNotEmptyAndNotInFuture(
                    $purchaseDate,
                    'purchaseDate',
                    $errors
                );
                $errors = $this->checkRequiredFields(
                    [$licNo, $holderName],
                    ['licNo', 'holderName'],
                    $errors
                );
                break;
            case self::TYPE_APPLIED:
            case self::TYPE_REFUSED:
            case self::TYPE_REVOKED:
            case self::TYPE_PUBLIC_INQUIRY:
                $errors = $this->checkRequiredFields(
                    [$licNo, $holderName],
                    ['licNo', 'holderName'],
                    $errors
                );
                break;
            default:
                $errors[] = [
                    'previousLicenceType' => [
                        self::ERROR_TYPE_NOT_IMPLEMENTED => 'Other licence type is not implemented'
                    ]
                ];
                break;
        }
        if (!$errors) {
            return true;
        }
        throw new ValidationException($errors);
    }

    /**
     * Is a date in the future
     *
     * @param string $value Datetime to test eg "2017-10-26"
     *
     * @return bool
     */
    protected function isDateInFuture($value)
    {
        $dateObject = new \DateTime($value);
        if ($dateObject > (new \DateTime())) {
            return true;
        }
        return false;
    }

    /**
     * Check a date
     *
     * @param string $dateToCheck Datetime to check eg "2017-10-26"
     * @param string $name        Name of the property being checked
     * @param array  $errors      Any existing error messages, to be merged
     *
     * @return array of error messages
     */
    protected function checkDateNotEmptyAndNotInFuture($dateToCheck, $name, $errors)
    {
        if (!$dateToCheck) {
            $errors[] = [
                $name => [
                    self::ERROR_FIELD_IS_REQUIRED => 'Field is required'
                ]
            ];
        } elseif ($this->isDateInFuture($dateToCheck)) {
            $errors[] = [
                $name => [
                    self::ERROR_DATE_IN_FUTURE => 'Date should not be in future'
                ]
            ];
        }
        return $errors;
    }

    /**
     * Check if a required value is missing
     *
     * @param string $field  Property value
     * @param string $name   Property name
     * @param array  $errors Existing error messages to be merged
     *
     * @return array of error messages
     */
    protected function checkRequiredField($field, $name, $errors)
    {
        if (!$field) {
            $errors[] = [
                $name => [
                    self::ERROR_FIELD_IS_REQUIRED => 'Field is required'
                ]
            ];
        }
        return $errors;
    }

    /**
     * Check a set of properties are valid
     *
     * @param array $fields Array of property values
     * @param array $names  Array of property names
     * @param array $errors Existing error messages to be merged
     *
     * @return array of error messages
     */
    protected function checkRequiredFields($fields, $names, $errors)
    {
        for ($i = 0; $i < count($fields); $i++) {
            $errors = $this->checkRequiredField($fields[$i], $names[$i], $errors);
        }
        return $errors;
    }

    /**
     * Check previous licence is not empty
     *
     * @param string $previousLicenceType Previous licence type, refData
     *
     * @return void
     * @throws ValidationException
     */
    protected function checkEmptyPreviousLicenceType($previousLicenceType)
    {
        if (!$previousLicenceType) {
            $errors[] = [
                'previousLicenceType' => [
                    self::ERROR_TYPE_IS_EMPTY => 'Processing of empty previousLicenceType is not implemented'
                ]
            ];
            throw new ValidationException($errors);
        }
    }

    /**
     * Update properties for a Transport Manager Licence
     *
     * @param string                  $role                    Role, refData
     * @param TransportManagerLicence $transportManagerLicence Transport Manager Licence entity
     * @param int                     $hoursPerWeek            Number of hours per week
     * @param string                  $licNo                   Licence number
     * @param string                  $operatingCentres        Operating centre names
     * @param int                     $totalAuthVehicles       Total auth vehicles
     *
     * @return void
     */
    public function updateOtherLicenceForTml(
        $role,
        $transportManagerLicence,
        $hoursPerWeek,
        $licNo,
        $operatingCentres,
        $totalAuthVehicles
    ) {
        $this->setRole($role);
        $this->setTransportManagerLicence($transportManagerLicence);
        $this->setHoursPerWeek($hoursPerWeek);
        $this->setLicNo($licNo);
        $this->setOperatingCentres($operatingCentres);
        $this->setTotalAuthVehicles($totalAuthVehicles);
    }

    /**
     * Get related organisation
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation|null|array
     */
    public function getRelatedOrganisation()
    {
        if ($this->getApplication()) {
            return $this->getApplication()->getLicence()->getOrganisation();
        }
        if ($this->getTransportManagerLicence()) {
            return $this->getTransportManagerLicence()->getLicence()->getOrganisation();
        }
        if ($this->getTransportManagerApplication()) {
            return $this->getTransportManagerApplication()->getApplication()->getLicence()->getOrganisation();
        }
        if ($this->getTransportManager()) {
            $relatedOrganisations = [];
            $tmApplications = $this->getTransportManager()->getTmApplications();
            foreach ($tmApplications as $tmApplication) {
                $relatedOrganisations[] = $tmApplication->getApplication()->getLicence()->getOrganisation();
            }
            return $relatedOrganisations;
        }

        return null;
    }
}
