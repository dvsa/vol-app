<?php

/**
 * Create EnvironmentalComplaint
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\EnvironmentalComplaint;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Cases\Complaint;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\EnvironmentalComplaint\CreateEnvironmentalComplaint as Cmd;

/**
 * Create EnvironmentalComplaint
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class CreateEnvironmentalComplaint extends AbstractCommandHandler implements
    AuthAwareInterface,
    TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Complaint';

    protected $extraRepos = ['Cases', 'ContactDetails'];

    /**
     * Creates complaint and associated entities
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        // create and save Environmental Complaint
        $complaint = $this->createComplaintObject($command);
        $this->getRepo()->save($complaint);

        $result->addId('environmentalComplaint', $complaint->getId());
        $result->addMessage('Environmental Complaint created');

        // create a task
        $taskResult = $this->handleSideEffect($this->createCreateTaskCommand($command));
        $result->merge($taskResult);

        return $result;
    }

    /**
     * Create the complaint object
     *
     * @return Complaint
     */
    private function createComplaintObject(Cmd $command)
    {
        $isCompliance = false;

        // create new contact details
        $contactDetails =  ContactDetails::create(
            $this->getRepo()->getRefdataReference(ContactDetails::CONTACT_TYPE_COMPLAINANT),
            $this->getRepo('ContactDetails')->populateRefDataReference(
                $command->getComplainantContactDetails()
            )
        );

        $complaint = new Complaint(
            $this->getRepo()->getReference(Cases::class, $command->getCase()),
            $isCompliance,
            $this->getRepo()->getRefdataReference($command->getStatus()),
            new \DateTime($command->getComplaintDate()),
            $contactDetails
        );
        $complaint->setDescription($command->getDescription());
        $complaint->populateClosedDate();

        if ($command->getOperatingCentres() !== null) {
            $operatingCentres = [];

            foreach ($command->getOperatingCentres() as $operatingCentreId) {
                $operatingCentres[] = $this->getRepo()->getReference(OperatingCentre::class, $operatingCentreId);
            }

            $complaint->setOperatingCentres($operatingCentres);
        }

        return $complaint;
    }

    /**
     * @return CreateTask
     */
    private function createCreateTaskCommand(Cmd $command)
    {
        /** @var Cases $case */
        $case = $this->getRepo('Cases')->fetchById($command->getCase(), Query::HYDRATE_OBJECT);

        $currentUser = $this->getCurrentUser();

        $data = [
            'category' => Task::CATEGORY_ENVIRONMENTAL,
            'subCategory' => Task::SUBCATEGORY_REVIEW_COMPLAINT,
            'description' => 'Review complaint',
            'actionDate' => $case->getLicence()->getReviewDate(),
            'assignedToUser' => $currentUser->getId(),
            'assignedToTeam' => $currentUser->getTeam()->getId(),
            'case' => $case->getId(),
        ];

        return CreateTask::create($data);
    }
}
