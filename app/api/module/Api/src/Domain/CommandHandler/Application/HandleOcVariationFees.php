<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee as CreateApplicationFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Handle Oc Variation Fees
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class HandleOcVariationFees extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Fee'];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Api\Domain\Command\Application\HandleOcVariationFees $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command);

        // OLCS-10953: don't invoke fee logic if application was created internally
        if ($application->createdInternally()) {
            return $this->result;
        }

        $applicationOcs = $application->getOperatingCentres();
        $licenceOcs = $application->getLicence()->getOperatingCentres();

        if ($this->feeApplies($applicationOcs, $licenceOcs, $application)) {
            $this->maybeCreateVariationFee($application);
        } else {
            $this->maybeCancelVariationFee($application);
        }

        return $this->result;
    }

    /**
     * Maybe create variation fee
     *
     * @param Application $application application
     *
     * @return void
     */
    private function maybeCreateVariationFee(Application $application)
    {
        if (!$application->hasApplicationFee()) {
            $data = [
                'id' => $application->getId(),
                'feeTypeFeeType' => FeeType::FEE_TYPE_VAR
            ];

            $this->result->merge($this->handleSideEffect(CreateApplicationFeeCmd::create($data)));
        }
    }

    /**
     * Maybe cancel variation fee
     *
     * @param Application $application application
     *
     * @return void
     */
    private function maybeCancelVariationFee(Application $application)
    {
        $fees = $this->getRepo('Fee')->fetchOutstandingFeesByApplicationId($application->getId());

        if (!empty($fees)) {
            /** @var Fee $fee */
            foreach ($fees as $fee) {
                $this->handleSideEffect(CancelFee::create(['id' => $fee->getId()]));
            }

            $this->result->addMessage(count($fees) . ' Fee(s) cancelled');
        }
    }

    /**
     * Fee applies
     *
     * @param array       $applicationOcs application operating centres
     * @param array       $licenceOcs     licence operaring centres
     * @param Application $application    application
     *
     * @return bool
     */
    private function feeApplies($applicationOcs, $licenceOcs, $application)
    {
        $isGoods = $application->isGoods();

        if ($isGoods) {
            if ($application->hasHgvAuthorisationIncreased()) {
                // if there's an increase in HGV authorisation, fee applies
                return true;
            }

            if ($application->hasLgvAuthorisationIncreased()) {
                // if there's an increase in LGV authorisation, fee applies
                return true;
            }

            if ($application->hasAuthTrailersIncrease()) {
                // if there's an increase in trailers authorisation, fee applies
                return true;
            }
        }

        // PSV - fee always applies on authorisation increase, regardless of OC changes
        if ($application->isPsv()) {
            if ($application->hasTotalVehicleAuthorisationIncreased()) {
                // if there's an increase in authorised vehicles, fee applies
                return true;
            }
        }

        foreach ($applicationOcs as $aoc) {
            switch ($aoc->getAction()) {
                case 'A':
                    // operating centre added, fee applies if this is a goods application
                    return $isGoods;
                case 'U':
                    // if there's an increase in auth. at a centre, fee applies
                    if ($this->hasIncreasedAuth($aoc, $licenceOcs)) {
                        return true;
                    }
                    break;
            }
        }
        return false;
    }

    /**
     * Has increased authority
     *
     * @param ApplicationOperatingCentre $aoc        application operating centre
     * @param array                      $licenceOcs licence operating centres
     *
     * @return bool
     */
    private function hasIncreasedAuth(ApplicationOperatingCentre $aoc, $licenceOcs)
    {
        /** @var LicenceOperatingCentre $loc */
        foreach ($licenceOcs as $loc) {
            if ($aoc->getOperatingCentre() === $loc->getOperatingCentre()) {
                if ($aoc->getNoOfVehiclesRequired() > $loc->getNoOfVehiclesRequired()) {
                    return true;
                }
                if ($aoc->getNoOfTrailersRequired() > $loc->getNoOfTrailersRequired()) {
                    return true;
                }
            }
        }

        return false;
    }
}
