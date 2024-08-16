<?php

/**
 * UpdateLicenceStatusRule.php
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LicenceStatusRule;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Pi\Decision as DecisionEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Class UpdateLicenceStatusRule
 *
 * Create a licence status rule.
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\LicenceStatusRule
 */
final class UpdateLicenceStatusRule extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'LicenceStatusRule';

    protected $extraRepos = ['Licence'];

    public function handleCommand(CommandInterface $command)
    {
        $statusRule = $this->getRepo()->fetchById($command->getId());

        if (!is_null($command->getStartDate())) {
            $statusRule->setStartDate(new \DateTime($command->getStartDate()));
        }

        if (!is_null($command->getEndDate())) {
            $statusRule->setEndDate(new \DateTime($command->getEndDate()));
        }

        $this->getRepo()->save($statusRule);

        $result = new Result();
        $result->addId('licence-status-rule', $statusRule->getId());
        $result->addMessage('Licence status rule updated successfully');

        $licence = $statusRule->getLicence();

        // update decisions
        $licence->setDecisions($this->buildArrayCollection(DecisionEntity::class, $command->getDecisions()));

        $this->getRepo('Licence')->save($licence);
        return $result;
    }
}
