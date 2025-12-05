<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\LicenceStatusAwareTrait;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser as OrganisationUserEntity;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Service\Document\ContextProviderInterface;
use Doctrine\ORM\EntityNotFoundException;

/**
 * Organisation Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="organisation",
 *    indexes={
 *        @ORM\Index(name="ix_organisation_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_organisation_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_organisation_type", columns={"type"}),
 *        @ORM\Index(name="ix_organisation_lead_tc_area_id", columns={"lead_tc_area_id"}),
 *        @ORM\Index(name="ix_organisation_name", columns={"name"}),
 *        @ORM\Index(name="ix_organisation_contact_details_id", columns={"contact_details_id"}),
 *        @ORM\Index(name="ix_organisation_irfo_contact_details_id", columns={"irfo_contact_details_id"})
 *    }
 * )
 */
class Organisation extends AbstractOrganisation implements ContextProviderInterface, OrganisationProviderInterface
{
    use LicenceStatusAwareTrait;

    public const ORG_TYPE_PARTNERSHIP = 'org_t_p';
    public const ORG_TYPE_OTHER = 'org_t_pa';
    public const ORG_TYPE_REGISTERED_COMPANY = 'org_t_rc';
    public const ORG_TYPE_LLP = 'org_t_llp';
    public const ORG_TYPE_SOLE_TRADER = 'org_t_st';
    public const ORG_TYPE_IRFO = 'org_t_ir';

    public const OPERATOR_CPID_ALL = 'op_cpid_all';

    public const ALLOWED_OPERATOR_LOCATION_NI = 'NI';
    public const ALLOWED_OPERATOR_LOCATION_GB = 'GB';

    protected $hasInforceLicences;

    /**
     * Has inforce licences
     *
     * @return bool
     */
    public function hasInforceLicences()
    {
        if ($this->hasInforceLicences === null) {
            $criteria = Criteria::create();
            $criteria->where($criteria->expr()->neq('inForceDate', null));

            $licences = $this->getLicences()->matching($criteria);

            $this->hasInforceLicences = !empty($licences->toArray());
        }

        return $this->hasInforceLicences;
    }

    /**
     * Get admin organisation users
     *
     * @return ArrayCollection|OrganisationUserEntity[]
     */
    public function getAdminOrganisationUsers()
    {
        $criteria = Criteria::create();
        $criteria->andWhere(
            $criteria->expr()->eq('isAdministrator', 'Y')
        );

        /** @var ArrayCollection $adminUsers */
        $adminUsers = $this->getOrganisationUsers()->matching($criteria);

        // unfortunately criteria doesn't work with a relations so need to filter manually
        $enabledOrgUsers = new ArrayCollection();

        /** @var OrganisationUserEntity $orgUser */
        foreach ($adminUsers as $orgUser) {
            try {
                $user = $orgUser->getUser();
                if (
                    $user instanceof UserEntity
                    && $user->getAccountDisabled() !== 'Y'
                ) {
                    $enabledOrgUsers->add($orgUser);
                }
            } catch (EntityNotFoundException) {
                // we may have the user id but will not be able to load it
                // because SoftDeleteable is used
            }
        }

        return $enabledOrgUsers;
    }

    /**
     * Whether a user can access permits
     */
    public function isEligibleForPermits()
    {
        $licences = $this->getLicences();

        /**
         * Iterate through the licences, looking for a (valid) goods licence
         * Stop as soon as we find one
         *
         * @var LicenceEntity $licence
         */
        foreach ($licences as $licence) {
            if ($licence->isEligibleForPermits()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Has even one Operator-admin users a corrent email address
     *
     * @return bool
     */
    public function hasAdminEmailAddresses()
    {
        $emailValidator = new \Laminas\Validator\EmailAddress();

        /** @var OrganisationUser $orgUser */
        foreach ($this->getAdminOrganisationUsers() as $orgUser) {
            $contactDetails = $orgUser->getUser()->getContactDetails();

            // Skip users with no contact details
            if ($contactDetails === null) {
                continue;
            }

            $email = $contactDetails->getEmailAddress();

            if (
                !empty($email)
                && $emailValidator->isValid($email)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is this organisation a sole trader
     *
     * @return bool
     */
    public function isSoleTrader()
    {
        return $this->getType()->getId() === self::ORG_TYPE_SOLE_TRADER;
    }

    /**
     * Is this organisation a partnership
     *
     * @return bool
     */
    public function isPartnership()
    {
        return $this->getType()->getId() === self::ORG_TYPE_PARTNERSHIP;
    }

    /**
     * Is this organisation a Ltd company
     *
     * @return bool
     */
    public function isLtd()
    {
        return $this->getType()->getId() === self::ORG_TYPE_REGISTERED_COMPANY;
    }

    /**
     * @return array
     * @deprecated
     */
    protected function getCalculatedValues()
    {
        return [
            'hasInforceLicences' => $this->hasInforceLicences(),
            /* prevent recursion via app -> licence -> organisation -> licence */
            'licences' => null,
        ];
    }

    /**
     * getCalculatedBundleValues
     *
     * @return array hasInforceLicences
     */
    protected function getCalculatedBundleValues()
    {
        return [
            'hasInforceLicences' => $this->hasInforceLicences(),
        ];
    }

    /**
     * Update Organisation
     *
     * @param $name             name
     * @param $companyNumber    company number
     * @param $firstName        first name
     * @param $lastName         last name
     * @param $isIrfo           ir info
     * @param $natureOfBusiness nature of business
     * @param $cpid             cpid
     * @param $allowEmail       allows email
     *
     */
    public function updateOrganisation(
        $name,
        $companyNumber,
        $firstName,
        $lastName,
        $isIrfo,
        $natureOfBusiness,
        $cpid,
        $allowEmail
    ) {
        $this->setCpid($cpid);
        $this->setNatureOfBusiness($natureOfBusiness);
        if ($isIrfo === 'Y' || ($this->getType() !== null && $this->getType()->getId() === self::ORG_TYPE_IRFO)) {
            $this->isIrfo = 'Y';
        } else {
            $this->isIrfo = 'N';
        }
        if ($companyNumber) {
            $this->companyOrLlpNo = $companyNumber;
        }
        if (!empty($lastName)) {
            $this->name = trim($firstName . ' ' . $lastName);
        } else {
            $this->name = $name;
        }

        $this->setAllowEmail($allowEmail);
    }

    /**
     * Is this organisation an unlicensed operator
     *
     * @return bool
     */
    public function isUnlicensed()
    {
        return (bool)$this->getIsUnlicensed();
    }

    /**
     * Has active licences
     *
     * @param string $goodsOrPsv Check only licences of a given goodsOrPsv value
     *
     * @return bool
     */
    public function hasActiveLicences($goodsOrPsv = null)
    {
        return !$this->getActiveLicences($goodsOrPsv)->isEmpty();
    }

    /**
     * Get active licences
     *
     * @param string $goodsOrPsv Return only licences of a given goodsOrPsv value
     *
     * @return ArrayCollection LicenceEntity[]
     */
    public function getActiveLicences($goodsOrPsv = null)
    {
        return $this->getLicences()->filter(
            function ($element) use ($goodsOrPsv) {
                $result = in_array($element->getStatus(), $this->getLicenceStatusesActive());

                if ($result && $goodsOrPsv !== null) {
                    $result = in_array($element->getGoodsOrPsv(), [$goodsOrPsv]);
                }

                return $result;
            }
        );
    }

    /**
     * Get related licences
     *
     * @return ArrayCollection
     */
    public function getRelatedLicences()
    {
        return $this->getLicences()->filter(
            fn($element) => in_array(
                $element->getStatus(),
                [
                    LicenceEntity::LICENCE_STATUS_NOT_SUBMITTED,
                    LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION,
                    LicenceEntity::LICENCE_STATUS_GRANTED,
                    LicenceEntity::LICENCE_STATUS_VALID,
                    LicenceEntity::LICENCE_STATUS_SUSPENDED,
                    LicenceEntity::LICENCE_STATUS_CURTAILED,
                ]
            )
        );
    }

    /**
     * Gets a licence from the organisation licences. Used by EBSR to check the licence is related to the organisation,
     * we return more than just a true/false, as the status is checked afterwards
     *
     * @param int $licNo Licence Number
     *
     * @return ArrayCollection
     */
    public function getLicenceByLicNo($licNo)
    {
        $criteria = Criteria::create();
        $criteria->where(
            $criteria->expr()->eq('licNo', $licNo)
        );

        return $this->getLicences()->matching($criteria);
    }

    /**
     * Get the disqualification linked to this organisation
     * NB DB schema is 1 to many, but it is only possible to have one disqualification record per organisation
     *
     * @return null|Disqualification
     */
    public function getDisqualification()
    {
        if ($this->getDisqualifications()->isEmpty()) {
            return null;
        }

        return $this->getDisqualifications()->first();
    }

    /**
     * Get the disqualification status
     *
     * @return string Disqualification constant STATUS_NONE, STATUS_ACTIVE or STATUS_INACTIVE
     */
    public function getDisqualificationStatus()
    {
        if ($this->getDisqualification() === null) {
            return Disqualification::STATUS_NONE;
        }

        return $this->getDisqualification()->getStatus();
    }

    /**
     * Determine is an organisation isMlh (Multiple Licence Holder has at least two valid Goods licences)
     * Note: Licences are considered valid if in one of the following states:
     *
     *  Under consideration
     *  Granted
     *  Valid
     *  Curtailed
     *  Suspended
     *
     *  Additional A/C:
     *  Where licence status is Under consideration or Granted, pull the operator type from the associated
     *  new application. (There should be only be one associated application)
     *  For the Valid, Curtailed or Suspended statuses, pull the operator type from the licence record
     *
     * @return bool
     */
    public function isMlh()
    {
        $totalValidLicences = $this->getLicences()->filter(
            fn($element) => in_array(
                $element->getStatus(),
                [
                    LicenceEntity::LICENCE_STATUS_VALID,
                    LicenceEntity::LICENCE_STATUS_CURTAILED,
                    LicenceEntity::LICENCE_STATUS_SUSPENDED,
                ]
            ) && in_array(
                $element->getGoodsOrPsv(),
                [
                    LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
                ]
            )
        )->count();

        //if there is more than one valid licence we can skip other checks
        if ($totalValidLicences > 1) {
            return true;
        }

        /** @var ArrayCollection $newLicences */
        $newLicences = $this->getLicences()->filter(
            fn($element) => in_array(
                $element->getStatus(),
                [
                    LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION,
                    LicenceEntity::LICENCE_STATUS_GRANTED,
                ]
            )
        );

        //if the number of valid licences, added to the number of potential remaining licences is less than one
        //then we can return false and skip the other checks
        if (($totalValidLicences + $newLicences->count()) <= 1) {
            return false;
        }

        /** @var LicenceEntity $licence */
        foreach ($newLicences as $licence) {
            $applications = $licence->getApplications();
            if ($applications->count() > 0 && $applications->first()->isGoods()) {
                $totalValidLicences++;
            }

            //if there is more than one valid licence we can skip the remaining checks
            if ($totalValidLicences > 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get All Outstanding applications for all licences
     * Status "under consideration" or "granted" and optionally "not submitted"
     *
     * @param bool $includeNotSubmitted Is include Not Submitted
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOutstandingApplications($includeNotSubmitted = false)
    {
        $applications = [];

        $licences = $this->getLicences();

        /** @var LicenceEntity $licence */
        foreach ($licences as $licence) {
            $outstandingApplications = $licence->getOutstandingApplications($includeNotSubmitted)->toArray();
            $applications = array_merge($applications, $outstandingApplications);
        }
        return new ArrayCollection($applications);
    }

    /**
     * Returns licences linked to this organisation for submissions
     * NOTE: In submissions, the licence the submission relates to is ALSO EXCLUDED. As these are 'linked licences'.
     *
     * @return ArrayCollection
     */
    public function getLinkedLicences()
    {
        return $this->getLicences()->filter(
            fn($element) => !in_array(
                $element->getStatus(),
                [
                    LicenceEntity::LICENCE_STATUS_NOT_SUBMITTED,
                    LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION,
                    LicenceEntity::LICENCE_STATUS_GRANTED,
                    LicenceEntity::LICENCE_STATUS_WITHDRAWN,
                    LicenceEntity::LICENCE_STATUS_REFUSED,
                ]
            )
        );
    }

    public function getContextValue()
    {
        return $this->getId();
    }

    /**
     * @inheritdoc
     */
    public function getRelatedOrganisation()
    {
        return $this;
    }

    /**
     * Gets email addresses for organisation administrator users
     *
     * @return array
     */
    public function getAdminEmailAddresses()
    {
        $adminUsers = $this->getAdminOrganisationUsers();
        $adminEmails = [];

        /** @var OrganisationUserEntity $orgUser */
        foreach ($adminUsers as $orgUser) {
            try {
                $contactDetails = $orgUser->getUser()->getContactDetails();

                // Skip users with no contact details (similar to soft-deleted users)
                if ($contactDetails === null) {
                    continue;
                }

                $adminEmails[] = $contactDetails->getEmailAddress();
            } catch (EntityNotFoundException) {
                //soft delete means no organisation user
                continue;
            }
        }

        return $adminEmails;
    }

    public function hasOperatorAdmin(): bool
    {
        $administratorUsers = $this->getAdminOrganisationUsers();

        /** @var OrganisationUserEntity $orgUser */
        foreach ($administratorUsers as $orgUser) {
            if ($orgUser->getUser()->isOperatorAdministrator()) {
                return true;
            }
        }

        return false;
    }

    /**
     * can only delete operator admin if more than one exists
     */
    public function canDeleteOperatorAdmin(): bool
    {
        $numOperatorAdmins = 0;
        $administratorUsers = $this->getAdminOrganisationUsers();

        /** @var OrganisationUserEntity $orgUser */
        foreach ($administratorUsers as $orgUser) {
            if ($orgUser->getUser()->isOperatorAdministrator()) {
                $numOperatorAdmins++;
            }

            if ($numOperatorAdmins > 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the latest Trading Name that hasnt been deleted
     *
     * @return string
     */
    public function getTradingAs()
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create()
            ->andWhere($expr->isNull('deletedDate'))
            ->orderBy(['createdOn' => Criteria::DESC])
            ->setMaxResults(1);
        $matches = $this->tradingNames->matching($criteria);
        if (!empty($matches)) {
            return $matches[0]->getName();
        }
        return '';
    }

    /**
     * Get allowed operator location
     *
     * @return string|null
     */
    public function getAllowedOperatorLocation()
    {
        $allowedOperatorLocation = null;
        $outstandingApplications = $this->getOutstandingApplications(true);
        if ($outstandingApplications->count() && $outstandingApplications[0]->getNiFlag() !== null) {
            $allowedOperatorLocation = $outstandingApplications[0]->getNiFlag() === 'Y' ?
                self::ALLOWED_OPERATOR_LOCATION_NI : self::ALLOWED_OPERATOR_LOCATION_GB;
        } else {
            $licences = $this->getLicences();
            /** @var LicenceEntity $licence */
            foreach ($licences as $licence) {
                if (
                    $licence->getStatus() &&
                    ($licence->getStatus()->getId() === LicenceEntity::LICENCE_STATUS_CANCELLED ||
                        $licence->getStatus()->getId() === LicenceEntity::LICENCE_STATUS_WITHDRAWN)
                ) {
                    continue;
                }
                if ($licence->getTrafficArea() !== null) {
                    $allowedOperatorLocation =
                        $licence->getTrafficArea()->getId() ===
                        TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE ?
                            self::ALLOWED_OPERATOR_LOCATION_NI : self::ALLOWED_OPERATOR_LOCATION_GB;
                    break;
                }
            }
        }

        return $allowedOperatorLocation;
    }

    /**
     * Has unlicenced licences
     *
     * @return bool
     */
    public function hasUnlicencedLicences()
    {
        $licences = $this->getLicences();
        if ($licences->isEmpty()) {
            return false;
        }

        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->contains('licNo', 'U'));

        return !empty($licences->matching($criteria)->toArray());
    }

    /**
     * Get licences eligible for irhp permits
     *
     * @return ArrayCollection
     */
    public function getEligibleIrhpLicences()
    {
        return $this->getLicences()->filter(
            fn($element) => ((string)$element->getGoodsOrPsv() === LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE) && in_array(
                $element->getStatus(),
                [
                    LicenceEntity::LICENCE_STATUS_VALID,
                    LicenceEntity::LICENCE_STATUS_SUSPENDED,
                    LicenceEntity::LICENCE_STATUS_CURTAILED,
                ]
            ) && in_array(
                $element->getLicenceType(),
                [
                    LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                    LicenceEntity::LICENCE_TYPE_RESTRICTED,
                ]
            )
        );
    }

    /**
     * Get licences eligible to apply for the specified irhp permit type/stock indexed by licence id
     */
    public function getEligibleIrhpLicencesForStock(IrhpPermitStock $stock): array
    {
        $licences = $this->getEligibleIrhpLicences();
        $licencesArr = [];

        /** @var LicenceEntity $licence */
        foreach ($licences as $licence) {
            $activeApplication = $licence->getActiveIrhpApplication($stock);

            $canMakeApplication = true;
            $activeApplicationId = null;

            if ($activeApplication !== null) {
                $canMakeApplication = false;
                $activeApplicationId = $activeApplication->getId();
            }

            $id = $licence->getId();

            $licencesArr[$id] = [
                'id' => $id,
                'licNo' => $licence->getLicNo(),
                'trafficArea' => $licence->getTrafficArea()->getName(),
                'isRestricted' => $licence->isRestricted(),
                'licenceTypeDesc' => $licence->getLicenceType()->getDescription(),
                'canMakeApplication' => $canMakeApplication,
                'activeApplicationId' => $activeApplicationId,
            ];
        }

        return $licencesArr;
    }

    /**
     * Determines if the organisation has ever submitted a licence application.
     *
     * @return bool
     */
    public function hasSubmittedLicenceApplication(): bool
    {
        if (count($this->getLicences()) === 0) {
            return false;
        }

        $licences = $this->getLicences()->filter(
            fn($element) => !in_array(
                $element->getStatus(),
                [
                    LicenceEntity::LICENCE_STATUS_NOT_SUBMITTED,
                    LicenceEntity::LICENCE_STATUS_CANCELLED,
                ]
            )
        );

        return (count($licences) !== 0);
    }
}
