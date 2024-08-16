<?php

namespace Dvsa\Olcs\Api\Entity\OperatingCentre;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * OperatingCentre Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="operating_centre",
 *    indexes={
 *        @ORM\Index(name="ix_operating_centre_address_id", columns={"address_id"}),
 *        @ORM\Index(name="ix_operating_centre_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_operating_centre_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_operating_centre_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class OperatingCentre extends AbstractOperatingCentre implements OrganisationProviderInterface
{
    protected $hasEnvironmentalComplaint;

    protected $hasOpposition;

    public function getHasEnvironmentalComplaint()
    {
        $this->hasEnvironmentalComplaint = 'N';

        /** @var \Dvsa\Olcs\Api\Entity\Cases\Complaint $complaint */
        foreach ($this->getComplaints() as $complaint) {
            if ($complaint->getClosedDate() === null && $complaint->isEnvironmentalComplaint()) {
                $this->hasEnvironmentalComplaint = 'Y';
                break;
            }
        }

        return $this->hasEnvironmentalComplaint;
    }

    public function getHasOpposition()
    {
        $notAllowedStatuses = [
            Application::APPLICATION_STATUS_GRANTED,
            Application::APPLICATION_STATUS_REFUSED,
            Application::APPLICATION_STATUS_NOT_TAKEN_UP,
            Application::APPLICATION_STATUS_WITHDRAWN,
        ];

        $this->hasOpposition = 'N';

        /** @var \Dvsa\Olcs\Api\Entity\Opposition\Opposition $opposition */
        foreach ($this->getOppositions() as $opposition) {
            // Only move on if is NOT withdrawn.
            if ($opposition->getIsWithdrawn() === false) {
                // Do we even have a linked application??
                /** @var Application $application */
                if ($application = $opposition->getCase()->getApplication()) {
                    // status is NONE of these
                    if (!in_array($application->getStatus()->getId(), $notAllowedStatuses)) {
                        $this->hasOpposition = 'Y';
                        break;
                    }
                }
            }
        }

        return $this->hasOpposition;
    }

    /**
     * Get organisation this entity is linked to
     *
     * @return null|\Dvsa\Olcs\Api\Entity\Organisation\Organisation
     */
    public function getRelatedOrganisation()
    {
        // Application could be different if the operating centre has been S4'd, therefore choose the most recent
        if ($this->getApplications()->first()) {
            return $this->getApplications()->last()->getRelatedOrganisation();
        }

        // not checking Operating centres linked to Licences at the moment as its not required

        return null;
    }
}
