<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Venue as VenueEntity;

/**
 * Impounding Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="impounding",
 *    indexes={
 *        @ORM\Index(name="ix_impounding_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_impounding_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_impounding_presiding_tc_id", columns={"presiding_tc_id"}),
 *        @ORM\Index(name="ix_impounding_outcome", columns={"outcome"}),
 *        @ORM\Index(name="ix_impounding_impounding_type", columns={"impounding_type"}),
 *        @ORM\Index(name="ix_impounding_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_impounding_venue_id", columns={"venue_id"})
 *    }
 * )
 */
class Impounding extends AbstractImpounding
{
    public const VENUE_OTHER = 'other';
    public const TYPE_PAPERWORK = 'impt_paper';
    public const TYPE_HEARING = 'impt_hearing';

    /**
     * Impounding constructor.
     *
     * @param Cases   $case           case entity
     * @param RefData $impoundingType impounding type
     *
     * @return void
     */
    public function __construct(Cases $case, RefData $impoundingType)
    {
        parent::__construct();

        $this->setCase($case);
        $this->setImpoundingType($impoundingType);
    }

    /**
     * Sets the venue and venueOther properties, since they are interdependent on each other.
     *
     * @param VenueEntity|null $venue      venue
     * @param String|null      $venueOther venue other info (freetext)
     *
     * @return Impounding
     */
    public function setVenueProperties($venue, $venueOther = null)
    {
        if (empty($venue)) {
            $this->venue = null;
            $this->venueOther = null;
        } else {
            if ($venue === self::VENUE_OTHER) {
                $this->venue = null;
                $this->venueOther = $venueOther;
            } else {
                $this->venue = $venue;
                $this->venueOther = null;
            }
        }

        return $this;
    }

    /**
     * Update impounding
     *
     * @param RefData          $impoundingType             impounding type
     * @param ArrayCollection  $impoundingLegislationTypes impounding legislation types
     * @param VenueEntity|null $venue                      venue
     * @param String           $venueOther                 venue other (freetext)
     * @param String           $applicationReceiptDate     application received date as string
     * @param String           $vrm                        vrm
     * @param String           $hearingDate                hearing date as string
     * @param RefData|null     $presidingTc                presiding tc doctrine reference
     * @param RefData|null     $outcome                    outcome doctrine reference
     * @param String           $outcomeSentDate            outcome sent date as string
     * @param String           $notes                      notes
     *
     * @return void
     */
    public function update(
        RefData $impoundingType,
        ArrayCollection $impoundingLegislationTypes,
        $venue,
        $venueOther,
        $applicationReceiptDate,
        $vrm,
        $hearingDate,
        $presidingTc,
        $outcome,
        $outcomeSentDate,
        $notes
    ) {
        $this->impoundingType = $impoundingType;
        $this->impoundingLegislationTypes = $impoundingLegislationTypes;
        $this->applicationReceiptDate = $this->processDate($applicationReceiptDate);
        $this->vrm = $vrm;
        $this->presidingTc = $presidingTc;
        $this->outcome = $outcome;
        $this->outcomeSentDate = $this->processDate($outcomeSentDate);
        $this->notes = $notes;

        //if it's paperwork only, clear the hearing and venue fields, then return
        if ($impoundingType->getId() === self::TYPE_PAPERWORK) {
            $this->hearingDate = null;
            $this->setVenueProperties(null);

            return;
        }

        $this->hearingDate = $this->processDate($hearingDate, \DateTime::ISO8601, false);
        $this->setVenueProperties($venue, $venueOther);
    }
}
