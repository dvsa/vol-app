<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContactDetails\PhoneContact;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command;

/**
 * Handler for CREATE a Phone contact
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'PhoneContact';
    protected $extraRepos = ['ContactDetails'];

    /**
     * Process handler
     *
     * @param Command\ContactDetail\PhoneContact\Create $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
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

        $entity = (new Entity\ContactDetails\PhoneContact($phoneContactType))
            ->setPhoneNumber($command->getPhoneNumber())
            ->setContactDetails($contactDetails);

        $phoneContactRepo->save($entity);

        $id = $entity->getId();

        return $this->result
            ->addId('phoneContact', $id)
            ->addMessage("Phone Contact '{$id}' created");
    }
}
