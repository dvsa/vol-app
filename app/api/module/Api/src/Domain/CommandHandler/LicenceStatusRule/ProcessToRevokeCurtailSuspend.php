<?php

/**
 * Process Licence Status Rules - to Revoke, Curtail and Suspend
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LicenceStatusRule;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class ProcessToRevokeCurtailSuspend extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'LicenceStatusRule';

    protected $commandMap = [
        \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_CURTAILED => Command\Licence\CurtailLicence::class,
        \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_SUSPENDED => Command\Licence\SuspendLicence::class,
        \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_REVOKED => Command\Licence\RevokeLicence::class
    ];

    public function handleCommand(CommandInterface $command)
    {
        // command not required, unset to remove PMD error
        unset($command);

        $result = new Result();
        $licencesToAction = $this->getRepo('LicenceStatusRule')->fetchRevokeCurtailSuspend(
            new \DateTime()
        );

        /* @var $licenceStatusRule \Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule */
        foreach ($licencesToAction as $licenceStatusRule) {
            // if licence is not valid, then continue
            if ($licenceStatusRule->getLicence()->getStatus()->getId() !== Licence::LICENCE_STATUS_VALID) {
                $result->addMessage(
                    "To Revoked, Curtailed, Suspended Licence Status Rule "
                    . "ID {$licenceStatusRule->getId()} licence is not valid"
                );
                continue;
            }

            // if no command is available then throw an error
            if (!isset($this->commandMap[$licenceStatusRule->getLicenceStatus()->getId()])) {
                throw new \Dvsa\Olcs\Api\Domain\Exception\RuntimeException(
                    "No Command is available to change licence to {$licenceStatusRule->getLicenceStatus()->getId()}"
                );
            }

            // call the sideeffect command eg Curtail
            $commandClass = $this->commandMap[$licenceStatusRule->getLicenceStatus()->getId()];
            $result->merge(
                $this->handleSideEffect(
                    $commandClass::create(
                        [
                            'id' => $licenceStatusRule->getLicence()->getId(),
                            'deleteLicenceStatusRules' => false
                        ]
                    )
                )
            );

            $licenceStatusRule->setStartProcessedDate(new \DateTime());
            $this->getRepo()->save($licenceStatusRule);

            $result->addMessage(
                "To Revoked, Curtailed, Suspended Licence Status Rule ID {$licenceStatusRule->getId()} success"
            );
        }

        return $result;
    }
}
