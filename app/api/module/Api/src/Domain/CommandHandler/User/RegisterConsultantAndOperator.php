<?php

/**
 * Register Operator and Consultant
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUserCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\User\Role as RoleEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve as RegisterUserSelfServeCommand;

final class RegisterConsultantAndOperator extends AbstractUserCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'User';
    protected $extraRepos = ['Role'];

    public function handleCommand(CommandInterface $command)
    {
        // Register the operator first, a new Org will be created.
        $this->result->merge($this->handleSideEffect(RegisterUserSelfServeCommand::create($command->getOperatorDetails())));

        // Get the newly created user entity
        $user = $this->getRepo()->fetchById($this->result->getId('user'));

        // Add the org ID of the newly created user/org to the consultant details, then register the consultant
        $consultantDetails = $command->getConsultantDetails();
        $consultantDetails['organisation'] = $user->getOrganisationUsers()->first()->getOrganisation()->getId();

        $this->result->merge($this->handleSideEffect(RegisterUserSelfServeCommand::create($consultantDetails)));

        // Get the new consultant user entity and set the correct role.
        $consultantUser = $this->getRepo()->fetchById($this->result->getId('user'));
        $operatorTcRole = $this->getRepo('Role')->fetchByRole(RoleEntity::ROLE_OPERATOR_TC);
        $consultantUser->setRoles(new ArrayCollection([$operatorTcRole]));
        $this->getRepo()->save($consultantUser);
        return $this->result;
    }
}
