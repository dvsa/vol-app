<?php

/**
 * Register Operator and Consultant
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\User\RegisterUserSelfserveByOrganisation as RegisterUserSelfServeByOrganisationCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUserCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\User\Role as RoleEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve as RegisterUserSelfServeCommand;

final class RegisterConsultantAndOperator extends AbstractUserCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'User';
    protected $extraRepos = ['Role'];

    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        //because this is being created by a transport consultant the operator admin will need to agree terms
        $operatorDetails = $command->getOperatorDetails();
        $operatorDetails['createdByConsultant'] = true;

        // Register the operator first, a new Org will be created.
        $this->result->merge($this->handleSideEffect(RegisterUserSelfServeCommand::create($operatorDetails)));

        // Get the newly created user entity
        $user = $this->getRepo()->fetchById($this->result->getId('user'));

        // Add the org ID of the newly created user/org to the consultant details, then register the consultant.
        // We use the internal RegisterUserSelfserveByOrganisation command (not the public, anonymous
        // RegisterUserSelfserve, which no longer accepts a raw organisation id — VOL-7370). The id here is
        // trusted: it belongs to the organisation we just created above, never to client input.
        $consultantDetails = $command->getConsultantDetails();
        $consultantDetails['organisation'] = $user->getOrganisationUsers()->first()->getOrganisation()->getId();

        $this->result->merge(
            $this->handleSideEffect(RegisterUserSelfServeByOrganisationCommand::create($consultantDetails))
        );

        /**
         * Get the new consultant user entity and set the correct role
         *
         * @var UserEntity $consultantUser
         * @var RoleEntity $operatorTcRole
         */
        $consultantUser = $this->getRepo()->fetchById($this->result->getId('user'));
        $operatorTcRole = $this->getRepo('Role')->fetchByRole(RoleEntity::ROLE_OPERATOR_TC);
        $consultantUser->setRoles(new ArrayCollection([$operatorTcRole]));

        //the consultant has already accepted terms and conditions
        $consultantUser->agreeTermsAndConditions();

        $this->getRepo()->save($consultantUser);
        return $this->result;
    }
}
