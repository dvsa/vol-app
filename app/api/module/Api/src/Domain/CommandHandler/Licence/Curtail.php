<?php

/**
 * Curtail a licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\RemoveLicenceStatusRulesForLicence;
use Dvsa\Olcs\Api\Entity\Pi\Decision as DecisionEntity;

/**
 * Curtail a licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Curtail extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command \Dvsa\Olcs\Transfer\Command\Licence\CurtailLicence */

        /* @var $licence Licence */
        $licence = $this->getRepo()->fetchUsingId($command);
        $licence->setStatus($this->getRepo()->getRefdataReference(Licence::LICENCE_STATUS_CURTAILED));
        $licence->setCurtailedDate(new \DateTime());

        $licence->setDecisions($this->buildArrayCollection(DecisionEntity::class, $command->getDecisions()));

        $this->getRepo()->save($licence);

        $result = new Result();

        if ($command->getDeleteLicenceStatusRules()) {
            $result->merge(
                $this->handleSideEffect(
                    RemoveLicenceStatusRulesForLicence::create(
                        [
                            'licence' => $licence
                        ]
                    )
                )
            );
        }

        $result->merge(
            $this->clearLicenceCacheSideEffect($licence->getId())
        );

        $result->addMessage("Licence ID {$licence->getId()} curtailed");

        return $result;
    }
}
