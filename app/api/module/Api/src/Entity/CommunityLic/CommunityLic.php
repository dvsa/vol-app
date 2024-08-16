<?php

namespace Dvsa\Olcs\Api\Entity\CommunityLic;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * CommunityLic Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="community_lic",
 *    indexes={
 *        @ORM\Index(name="ix_community_lic_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_community_lic_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_community_lic_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_community_lic_com_lic_status", columns={"status"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_community_lic_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class CommunityLic extends AbstractCommunityLic
{
    public const STATUS_PENDING = 'cl_sts_pending';
    public const STATUS_ACTIVE = 'cl_sts_active';
    public const STATUS_EXPIRED = 'cl_sts_expired';
    public const STATUS_WITHDRAWN = 'cl_sts_withdrawn';
    public const STATUS_SUSPENDED = 'cl_sts_suspended';
    public const STATUS_ANNUL = 'cl_sts_annulled';
    public const STATUS_RETURNDED = 'cl_sts_returned';

    public const PREFIX_GB = 'UKGB';
    public const PREFIX_NI = 'UKNI';

    public const ERROR_OFFICE_COPY_EXISTS = 'CL_OC_EXISTS';
    public const ERROR_CANT_ANNUL = 'CL_CANT_ANNUL';
    public const ERROR_CANT_RESTORE = 'CL_CANT_RESTORE';
    public const ERROR_CANT_REPRINT = 'CL_CANT_REPRINT';
    public const ERROR_CANT_STOP = 'CL_CANT_STOP';
    public const ERROR_START_DATE_EMPTY = 'CL_START_DATE_EMPTY';
    public const ERROR_END_DATE_WRONG = 'CL_END_DATE_WRONG';

    /**
     * Update community licence
     *
     * @param array $data data
     *
     * @return void
     */
    public function updateCommunityLic($data)
    {
        $this->setStatus($data['status']);
        if (isset($data['specifiedDate'])) {
            $this->setSpecifiedDate($data['specifiedDate']);
        }
        $this->setSerialNoPrefix($data['serialNoPrefix']);
        $this->setLicence($data['licence']);
        $this->setIssueNo($data['issueNo']);
    }

    /**
     * Change status and expiry date
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status     status
     * @param string                               $expiryDate string
     *
     * @return void
     */
    public function changeStatusAndExpiryDate($status, $expiryDate = '')
    {
        $this->setStatus($status);
        if ($expiryDate !== '') {
            $this->setExpiredDate($expiryDate);
        }
    }

    /**
     * Get future suspension
     *
     * @return array|null
     */
    public function getFutureSuspension()
    {
        if ($this->getStatus()->getId() === self::STATUS_ACTIVE) {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->gt('startDate', new \DateTime()))
                ->setMaxResults(1);
            $suspension = $this->getCommunityLicSuspensions()->matching($criteria)->current();
            if ($suspension) {
                return [
                    'startDate' => $suspension->getStartDate(),
                    'endDate' => $suspension->getEndDate(),
                    'reasons' => $this->prepareReasons($suspension),
                    'id' => $suspension->getId(),
                    'version' => $suspension->getVersion()
                ];
            }
        }
        return null;
    }

    /**
     * Get current suspension
     *
     * @return array|null
     */
    public function getCurrentSuspension()
    {
        if ($this->getStatus()->getId() === self::STATUS_SUSPENDED) {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->lte('startDate', new \DateTime()))
                ->setMaxResults(1);
            $suspension = $this->getCommunityLicSuspensions()->matching($criteria)->current();
            if ($suspension) {
                return [
                    'startDate' => $suspension->getStartDate(),
                    'endDate' => $suspension->getEndDate(),
                    'reasons' => $this->prepareReasons($suspension),
                    'id' => $suspension->getId(),
                    'version' => $suspension->getVersion()
                ];
            }
        }
        return null;
    }

    /**
     * Prepare reasons
     *
     * @param CommunityLicSuspension $suspension suspension
     *
     * @return array
     */
    private function prepareReasons($suspension)
    {
        $reasons = $suspension->getCommunityLicSuspensionReasons();
        $retv = [];
        foreach ($reasons as $reason) {
            $retv[] = $reason->getType()->getId();
        }
        return $retv;
    }

    /**
     * Get current withdrawal
     *
     * @return array|null
     */
    public function getCurrentWithdrawal()
    {
        if ($this->getStatus()->getId() === self::STATUS_WITHDRAWN) {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->lte('startDate', new \DateTime()))
                ->setMaxResults(1);
            $withdrawal = $this->getCommunityLicWithdrawals()->matching($criteria)->current();
            if ($withdrawal) {
                return [
                    'startDate' => $withdrawal->getStartDate(),
                    'id' => $withdrawal->getId()
                ];
            }
        }
        return null;
    }

    /**
     * Calculated values to be added to a bundle
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'futureSuspension' => $this->getFutureSuspension(),
            'currentSuspension' => $this->getCurrentSuspension(),
            'currentWithdrawal' => $this->getCurrentWithdrawal()
        ];
    }

    /**
     * Whether the community licence is active
     *
     * @return bool
     */
    public function isActive()
    {
        return ($this->getStatus()->getId() === self::STATUS_ACTIVE);
    }
}
