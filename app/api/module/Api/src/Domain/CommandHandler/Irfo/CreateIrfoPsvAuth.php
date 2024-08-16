<?php

/**
 * Create IrfoPsvAuth
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthNumber;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Transfer\Command\Irfo\CreateIrfoPsvAuth as Cmd;

/**
 * Create IrfoPsvAuth
 */
final class CreateIrfoPsvAuth extends AbstractCommandHandler implements TransactionedInterface
{
    use IrfoPsvAuthFeeTrait;

    protected $repoServiceName = 'IrfoPsvAuth';

    protected $extraRepos = ['IrfoPsvAuthNumber', 'FeeType'];

    public function handleCommand(CommandInterface $command)
    {
        // create and save a record
        $irfoPsvAuth = $this->createIrfoPsvAuthObject($command);
        $this->getRepo()->save($irfoPsvAuth);

        // deal with IrfoFileNo
        $irfoPsvAuth->populateFileNo();

        // deal with IrfoFeeId
        $irfoPsvAuth->populateIrfoFeeId();

        $this->getRepo()->save($irfoPsvAuth);

        // deal with IrfoPsvAuthNumbers
        if ($command->getIrfoPsvAuthNumbers() !== null) {
            foreach ($command->getIrfoPsvAuthNumbers() as $irfoPsvAuthNumber) {
                if (!empty($irfoPsvAuthNumber['name'])) {
                    // create
                    $irfoPsvAuthNumberEntity = new IrfoPsvAuthNumber($irfoPsvAuth, $irfoPsvAuthNumber['name']);
                    $this->getRepo('IrfoPsvAuthNumber')->save($irfoPsvAuthNumberEntity);
                }
            }
        }

        $result = new Result();
        $result->addId('irfoPsvAuth', $irfoPsvAuth->getId());
        $result->addMessage('IRFO PSV Auth created successfully');

        // generate application fee
        $result->merge($this->generateApplicationFee($irfoPsvAuth));

        return $result;
    }

    /**
     * @return IrfoPsvAuth
     */
    private function createIrfoPsvAuthObject(Cmd $command)
    {
        $organisation = $this->getRepo()->getReference(Organisation::class, $command->getOrganisation());
        $type = $this->getRepo()->getReference(IrfoPsvAuthType::class, $command->getIrfoPsvAuthType());
        $status = $this->getRepo()->getRefdataReference(IrfoPsvAuth::STATUS_PENDING);

        $irfoPsvAuth = new IrfoPsvAuth($organisation, $type, $status);

        // set IRFO file number as it's not nullable
        // and it requires the entity to be saved first before it can be set correctly
        // as entity id is part of the IrfoFileNo
        $irfoPsvAuth->setIrfoFileNo('');

        $irfoPsvAuth->setValidityPeriod($command->getValidityPeriod());
        $irfoPsvAuth->setInForceDate(new \DateTime($command->getInForceDate()));
        $irfoPsvAuth->setServiceRouteFrom($command->getServiceRouteFrom());
        $irfoPsvAuth->setServiceRouteTo($command->getServiceRouteTo());
        $irfoPsvAuth->setJourneyFrequency($this->getRepo()->getRefdataReference($command->getJourneyFrequency()));
        $irfoPsvAuth->setCopiesRequired($command->getCopiesRequired());
        $irfoPsvAuth->setCopiesRequiredTotal($command->getCopiesRequiredTotal());

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

        return $irfoPsvAuth;
    }
}
