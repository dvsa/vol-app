<?php

/**
 * RemoveLicenceStatusRulesForLicence.php
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LicenceStatusRule;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\LicenceStatusRule\DeleteLicenceStatusRule as DeleteRule;

/**
 * Remove licence status rules for a licence.
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
final class RemoveLicenceStatusRulesForLicence extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'LicenceStatusRule';

    protected $extraRepos = ['Licence'];

    public function handleCommand(CommandInterface $command)
    {
        $rules = $this->getRepo()->fetchForLicence($command->getLicence());

        $result = new Result();
        foreach ($rules as $statusRule) {
            $result->merge(
                $this->handleSideEffect(
                    DeleteRule::create(
                        [
                            'id' => $statusRule->getId()
                        ]
                    )
                )
            );
        }

        if (!empty($rules)) {
            $licence = $statusRule->getLicence();

            // remove decisions
            $licence->setDecisions($this->buildArrayCollection('', []));

            $this->getRepo('Licence')->save($licence);
        }

        return $result;
    }
}
