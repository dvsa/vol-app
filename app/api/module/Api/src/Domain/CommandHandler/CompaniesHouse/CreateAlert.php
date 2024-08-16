<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler as DomainAbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert as AlertEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Companies House CreateAlert
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class CreateAlert extends DomainAbstractCommandHandler
{
    protected $repoServiceName = 'CompaniesHouseAlert';

    protected $extraRepos = ['Organisation'];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();
        $companyNumber = $command->getCompanyNumber();

        try {
            $organisations = $this->getOrganisation($companyNumber);
        } catch (NotFoundException) {
            $result->addMessage("Organisation(s) not found for company $companyNumber, no alert created");
            return $result;
        }

        foreach ($organisations as $organisation) {
            $alert = new AlertEntity();
            $alert
                ->setCompanyOrLlpNo($companyNumber)
                ->setOrganisation($organisation);

            foreach ($command->getReasons() as $reason) {
                $reasonRefdata = $this->getRepo()->getRefdataReference($reason);
                $alert->addReason($reasonRefdata);
            }

            $this->getRepo('CompaniesHouseAlert')->save($alert);

            $alertId = $alert->getId();
            $result
                ->addId('companiesHouseAlert' . $alertId, $alertId)
                ->addMessage('Alert created: ' . json_encode($command->getReasons()));
        }

        return $result;
    }

    /**
     * Get organisation
     *
     * @param string $companyNumber company number
     *
     * @return array
     */
    protected function getOrganisation($companyNumber)
    {
        return $this->getRepo('Organisation')->getByCompanyOrLlpNo($companyNumber);
    }
}
