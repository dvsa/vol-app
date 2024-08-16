<?php

/**
 * Update IrfoDetails
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Organisation\UpdateTradingNames;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPartner;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * Update IrfoDetails
 */
final class UpdateIrfoDetails extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Organisation';

    protected $extraRepos = ['ContactDetails', 'IrfoPartner'];

    public function handleCommand(CommandInterface $command)
    {
        $org = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        if ($command->getIrfoNationality() !== null) {
            $org->setIrfoNationality(
                $this->getRepo()->getReference(Country::class, $command->getIrfoNationality())
            );
        }

        if ($command->getIrfoContactDetails() !== null) {
            if ($org->getIrfoContactDetails() instanceof ContactDetails) {
                // update existing contact details
                $org->getIrfoContactDetails()->update(
                    $this->getRepo('ContactDetails')->populateRefDataReference(
                        $command->getIrfoContactDetails()
                    )
                );
            } else {
                // create new contact details
                $org->setIrfoContactDetails(
                    ContactDetails::create(
                        $this->getRepo()->getRefdataReference(ContactDetails::CONTACT_TYPE_IRFO_OPERATOR),
                        $this->getRepo('ContactDetails')->populateRefDataReference(
                            $command->getIrfoContactDetails()
                        )
                    )
                );
            }
        }

        $this->getRepo()->save($org);

        if ($command->getTradingNames() !== null) {
            // deal with TradingNames
            $this->processTradingNames($org, $command->getTradingNames());
        }

        if ($command->getIrfoPartners() !== null) {
            // deal with IrfoPartners
            $this->processIrfoPartners($org, $command->getIrfoPartners());
        }

        $result = new Result();
        $result->addId('organisation', $org->getId());
        $result->addMessage('IRFO Details updated successfully');

        return $result;
    }

    /**
     * @return array
     */
    private function processTradingNames(Organisation $org, array $tradingNames)
    {
        return $this->handleSideEffect(
            UpdateTradingNames::create(
                [
                    'organisation' => $org->getId(),
                    'tradingNames' => array_column($tradingNames, 'name')
                ]
            )
        );
    }

    /**
     * @return array
     */
    private function processIrfoPartners(Organisation $org, array $irfoPartners)
    {
        $reduced = [];

        foreach ($irfoPartners as $irfoPartner) {
            if (empty($irfoPartner['name'])) {
                // filter out empty values
                continue;
            }

            if (!empty($irfoPartner['id'])) {
                // update
                $irfoPartnerEntity = $this->getRepo('IrfoPartner')->fetchById(
                    $irfoPartner['id'],
                    Query::HYDRATE_OBJECT,
                    $irfoPartner['version']
                );
                $irfoPartnerEntity->setName($irfoPartner['name']);
            } else {
                // create
                $irfoPartnerEntity = new IrfoPartner(
                    $org,
                    $irfoPartner['name']
                );
            }

            $this->getRepo('IrfoPartner')->save($irfoPartnerEntity);
            $reduced[] = $irfoPartnerEntity->getId();
        }

        // remove the rest
        foreach ($org->getIrfoPartners() as $irfoPartnerEntity) {
            if (!in_array($irfoPartnerEntity->getId(), $reduced)) {
                $this->getRepo('IrfoPartner')->delete($irfoPartnerEntity);
            }
        }

        return $reduced;
    }
}
