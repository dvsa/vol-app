<?php

namespace Dvsa\Olcs\Api\Entity\TrafficArea;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Publication\Recipient as RecipientEntity;
use Doctrine\Common\Collections\Criteria;

/**
 * TrafficArea Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="traffic_area",
 *    indexes={
 *        @ORM\Index(name="ix_traffic_area_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_traffic_area_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_traffic_area_contact_details_id", columns={"contact_details_id"})
 *    }
 * )
 */
class TrafficArea extends AbstractTrafficArea
{
    public const NORTH_EASTERN_TRAFFIC_AREA_CODE    = 'B';
    public const NORTH_WESTERN_TRAFFIC_AREA_CODE    = 'C';
    public const WEST_MIDLANDS_TRAFFIC_AREA_CODE    = 'D';
    public const EASTERN_TRAFFIC_AREA_CODE          = 'F';
    public const WELSH_TRAFFIC_AREA_CODE            = 'G';
    public const WESTERN_TRAFFIC_AREA_CODE          = 'H';
    public const SE_MET_TRAFFIC_AREA_CODE           = 'K';
    public const SCOTTISH_TRAFFIC_AREA_CODE         = 'M';
    public const NORTHERN_IRELAND_TRAFFIC_AREA_CODE = 'N';

    public const GB_TA_IDS = [
        self::NORTH_EASTERN_TRAFFIC_AREA_CODE,
        self::NORTH_WESTERN_TRAFFIC_AREA_CODE,
        self::WEST_MIDLANDS_TRAFFIC_AREA_CODE,
        self::EASTERN_TRAFFIC_AREA_CODE,
        self::WELSH_TRAFFIC_AREA_CODE,
        self::WESTERN_TRAFFIC_AREA_CODE,
        self::SE_MET_TRAFFIC_AREA_CODE,
        self::SCOTTISH_TRAFFIC_AREA_CODE
    ];

    public const NI_TA_IDS = [
        self::NORTHERN_IRELAND_TRAFFIC_AREA_CODE
    ];

    /**
     * Gets the recipients for a publication, formatted ready for Laminas mail
     *
     * @param string $isPolice Y or N depending on whether this is a police document
     * @param string $pubType  A&D or N&P
     *
     * @return array
     */
    public function getPublicationRecipients($isPolice, $pubType)
    {
        /* the commented code can't currently be used due to a bug with Doctrine Criteria when used with
         * many-to-many collections. The links below describe the issue. Once this has been fixed in our version of
         * Doctrine, uncommenting this code and removing the if ($recipient->getIsPolice() === $isPolice) check found
         * below, should improve performance on lists with a large number of recipients
         *
         * @link https://github.com/doctrine/doctrine2/issues/5644
         * @link https://github.com/doctrine/doctrine2/pull/5669/files
         **/
        //$expr = Criteria::expr();
        //$criteria = Criteria::create();
        //$criteria->where($expr->eq('isPolice', $isPolice));
        //$matchedRecipients = $this->recipients->matching($criteria);
        $matchedRecipients = $this->getRecipients(); //remove when code above is uncommented
        $checkPubMethod = ($pubType === 'A&D' ? 'getSendAppDecision' : 'getSendNoticesProcs');

        $recipients = [];

        /** @var RecipientEntity $recipient */
        foreach ($matchedRecipients as $recipient) {
            $email = $recipient->getEmailAddress();

            //remove this if statement once the doctrine criteria code is uncommented
            if ($recipient->getIsPolice() === $isPolice && $recipient->$checkPubMethod() === 'Y' && !empty($email)) {
                $recipients[$email] = $recipient->getContactName();
            }
        }

        return $recipients;
    }
}
