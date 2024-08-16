<?php

/**
 * Licence Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Licence Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class Overview extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    public function handleCommand(CommandInterface $command)
    {
        /** @var LicenceEntity $licence */
        $licence = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $result = new Result();

        $this->setLeadTcArea($licence, $command);

        if (!is_null($command->getReviewDate())) {
            $licence->setReviewDate(new \DateTime($command->getReviewDate()));
        }
        if (!is_null($command->getExpiryDate())) {
            $licence->setExpiryDate(new \DateTime($command->getExpiryDate()));
        }
        if (!is_null($command->getTranslateToWelsh())) {
            $licence->setTranslateToWelsh($command->getTranslateToWelsh());
        }

        $this->getRepo()->save($licence);
        $result->merge(
            $this->clearLicenceCacheSideEffect($licence->getId())
        );

        $result
            ->addId('licence', $licence->getId())
            ->addMessage('Licence updated');

        return $result;
    }

    protected function setLeadTcArea(LicenceEntity $licence, CommandInterface $command)
    {
        if (!is_null($command->getLeadTcArea())) {
            /** @var Organisation $organisation */
            $organisation = $licence->getOrganisation();
            $organisation->setLeadTcArea(
                $this->getRepo()->getReference(TrafficAreaEntity::class, $command->getLeadTcArea())
            );
        }
    }
}
