<?php

/**
 * Update Environmental Complaint
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\EnvironmentalComplaint;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Complaint
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class UpdateEnvironmentalComplaint extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Complaint';

    protected $extraRepos = ['ContactDetails'];

    /**
     * Update complaint
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $complaint = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $complaint->setComplaintDate(new \DateTime($command->getComplaintDate()));
        $complaint->setDescription($command->getDescription());
        $complaint->setStatus($this->getRepo()->getRefdataReference($command->getStatus()));
        $complaint->populateClosedDate();

        if ($command->getOperatingCentres() !== null) {
            $operatingCentres = [];

            foreach ($command->getOperatingCentres() as $operatingCentreId) {
                $operatingCentres[] = $this->getRepo()->getReference(OperatingCentre::class, $operatingCentreId);
            }

            $complaint->setOperatingCentres($operatingCentres);
        }

        if ($complaint->getComplainantContactDetails() instanceof ContactDetails) {
            // update existing contact details
            $complaint->getComplainantContactDetails()->update(
                $this->getRepo('ContactDetails')->populateRefDataReference(
                    $command->getComplainantContactDetails()
                )
            );
        } else {
            // create new contact details
            $complaint->setComplainantContactDetails(
                ContactDetails::create(
                    $this->getRepo()->getRefdataReference(ContactDetails::CONTACT_TYPE_COMPLAINANT),
                    $this->getRepo('ContactDetails')->populateRefDataReference(
                        $command->getComplainantContactDetails()
                    )
                )
            );
        }

        $this->getRepo()->save($complaint);

        $result = new Result();
        $result->addId('environmentalComplaint', $complaint->getId());
        $result->addMessage('Environmental Complaint updated');

        return $result;
    }
}
