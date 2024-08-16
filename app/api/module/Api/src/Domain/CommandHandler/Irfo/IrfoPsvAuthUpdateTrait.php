<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthNumber;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType;
use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoPsvAuth as UpdateIrfoPsvAuthCmd;

/**
 * IRFO PSV Auth Update Trait
 */
trait IrfoPsvAuthUpdateTrait
{
    /**
     * Common IRFO PSV Auth update
     *
     * @return IrfoPsvAuth
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function updateIrfoPsvAuth(UpdateIrfoPsvAuthCmd $command)
    {
        $irfoPsvAuth = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $inForceDate = $command->getInForceDate() !== null ? new \DateTime($command->getInForceDate()) : new \DateTime('now');

        $irfoPsvAuth->update(
            $this->getRepo()->getReference(IrfoPsvAuthType::class, $command->getIrfoPsvAuthType()),
            $command->getValidityPeriod(),
            $inForceDate,
            $command->getServiceRouteFrom(),
            $command->getServiceRouteTo(),
            $this->getRepo()->getRefdataReference($command->getJourneyFrequency()),
            $command->getCopiesRequired(),
            $command->getCopiesRequiredTotal()
        );

        if ($command->getExpiryDate() !== null) {
            $irfoPsvAuth->setExpiryDate(new \DateTime($command->getExpiryDate()));
        }

        if ($command->getApplicationSentDate() !== null) {
            $irfoPsvAuth->setApplicationSentDate(new \DateTime($command->getApplicationSentDate()));
        }

        if ($command->getCountrys() !== null) {
            $countries = [];

            foreach ($command->getCountrys() as $countryId) {
                $countries[] = $this->getRepo()->getReference(Country::class, $countryId);
            }

            $irfoPsvAuth->setCountrys($countries);
        }

        if ($command->getIsFeeExemptApplication() !== null) {
            $irfoPsvAuth->setIsFeeExemptApplication($command->getIsFeeExemptApplication());
        }

        if ($command->getIsFeeExemptAnnual() !== null) {
            $irfoPsvAuth->setIsFeeExemptAnnual($command->getIsFeeExemptAnnual());
        }

        if ($command->getExemptionDetails() !== null) {
            $irfoPsvAuth->setExemptionDetails($command->getExemptionDetails());
        }

        // deal with IrfoPsvAuthNumbers
        $this->processIrfoPsvAuthNumbers($irfoPsvAuth, $command->getIrfoPsvAuthNumbers());

        return $irfoPsvAuth;
    }

    /**
     * @return array
     */
    private function processIrfoPsvAuthNumbers(IrfoPsvAuth $irfoPsvAuth, array $irfoPsvAuthNumbers)
    {
        $this->extraRepos[] = 'IrfoPsvAuthNumber';

        $reduced = [];

        foreach ($irfoPsvAuthNumbers as $irfoPsvAuthNumber) {
            if (empty($irfoPsvAuthNumber['name'])) {
                // filter out empty values
                continue;
            }

            if (!empty($irfoPsvAuthNumber['id'])) {
                // update
                $irfoPsvAuthNumberEntity = $this->getRepo('IrfoPsvAuthNumber')->fetchById(
                    $irfoPsvAuthNumber['id'],
                    Query::HYDRATE_OBJECT,
                    $irfoPsvAuthNumber['version']
                );
                $irfoPsvAuthNumberEntity->setName($irfoPsvAuthNumber['name']);
            } else {
                // create
                $irfoPsvAuthNumberEntity = new IrfoPsvAuthNumber(
                    $irfoPsvAuth,
                    $irfoPsvAuthNumber['name']
                );
            }

            $this->getRepo('IrfoPsvAuthNumber')->save($irfoPsvAuthNumberEntity);
            $reduced[] = $irfoPsvAuthNumberEntity->getId();
        }

        // remove the rest records
        foreach ($irfoPsvAuth->getIrfoPsvAuthNumbers() as $irfoPsvAuthNumberEntity) {
            if (!in_array($irfoPsvAuthNumberEntity->getId(), $reduced)) {
                $this->getRepo('IrfoPsvAuthNumber')->delete($irfoPsvAuthNumberEntity);
            }
        }

        return $reduced;
    }
}
