<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContactDetails\PhoneContact;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command;

/**
 * Handler for UPDATE a Phone contact
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'PhoneContact';
    protected $extraRepos = ['ContactDetails'];

    /**
     * Process handler
     *
     * @param Command\ContactDetail\PhoneContact\Update $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(Command\CommandInterface $command)
    {
        $phoneContactRepo = $this->getRepo();

        $contactDetails = $this->getRepo('ContactDetails')
            ->fetchById(
                $command->getContactDetailsId()
            );

        $phoneContactType = $phoneContactRepo->getRefdataReference(
            $command->getPhoneContactType()
        );

        /** @var \Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact $entity */
        $entity = $phoneContactRepo->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $phoneContactRepo->lock($entity, $command->getVersion());

        $entity
            ->setContactDetails($contactDetails)
            ->setPhoneContactType($phoneContactType)
            ->setPhoneNumber($command->getPhoneNumber());

        $phoneContactRepo->save($entity);

        return $this->result->addMessage("Phone contact '{$entity->getId()}' updated");
    }
}
