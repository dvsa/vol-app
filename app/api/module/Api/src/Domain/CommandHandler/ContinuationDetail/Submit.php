<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\Submit as Command;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Transfer\Command\Licence\ContinueLicence;

/**
 * Submit Continuation Detail
 */
final class Submit extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ContinuationDetail';

    protected $extraRepos = ['Fee'];

    /**
     * Handle command
     *
     * @param CommandInterface $command DTO command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command Command */

        /* @var $continuationDetail ContinuationDetail */
        $continuationDetail = $this->getRepo()->fetchById(
            $command->getId(),
            \Doctrine\ORM\Query::HYDRATE_OBJECT,
            $command->getVersion()
        );

        // If signatureType is null, then must be printing and signing declaration.
        // If using Verify, signatureType would have already been set to SIG_DIGITAL_SIGNATURE
        if ($continuationDetail->getSignatureType() === null) {
            $continuationDetail->setSignatureType(
                $this->getRepo()->getRefdataReference(RefData::SIG_PHYSICAL_SIGNATURE)
            );
        }
        $continuationDetail->setIsDigital(true);

        // set default values if they haven't already been set
        if ($continuationDetail->getTotAuthVehicles() === null) {
            $continuationDetail->setTotAuthVehicles($continuationDetail->getLicence()->getTotAuthVehicles());
        }
        if ($continuationDetail->getTotCommunityLicences() === null) {
            $continuationDetail->setTotCommunityLicences(
                $continuationDetail->getLicence()->getTotCommunityLicences()
            );
        }
        if ($continuationDetail->getTotPsvDiscs() === null) {
            $continuationDetail->setTotPsvDiscs($continuationDetail->getLicence()->getPsvDiscsNotCeased()->count());
        }
        $this->getRepo()->save($continuationDetail);

        // If there are no continuation fees then continue the licence
        $continuationFees = $this->getRepo('Fee')->fetchOutstandingContinuationFeesByLicenceId(
            $continuationDetail->getLicence()->getId()
        );
        if (count($continuationFees) === 0) {
            $this->result->merge(
                $this->handleSideEffect(
                    ContinueLicence::create(
                        [
                            'id' => $continuationDetail->getLicence()->getId(),
                            'version' => $continuationDetail->getLicence()->getVersion(),
                        ]
                    )
                )
            );
        }

        $this->result->addId('continuationDetail', $continuationDetail->getId());
        $this->result->addMessage('ContinuationDetail submitted');

        return $this->result;
    }
}
