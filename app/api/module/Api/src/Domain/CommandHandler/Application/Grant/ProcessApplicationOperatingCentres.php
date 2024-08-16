<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteApplicationLinks;
use Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteConditionUndertakings;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Util\EntityCloner;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre as Aoc;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre as Loc;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Process Application Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ProcessApplicationOperatingCentres extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['LicenceOperatingCentre', 'ApplicationOperatingCentre'];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Api\Domain\Command\Application\Grant\ProcessApplicationOperatingCentres $command command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);
        $licence = $application->getLicence();

        $applicationOperatingCentres = $application->getOperatingCentres();

        $add = $update = $delete = 0;

        /** @var Aoc $aoc */
        foreach ($applicationOperatingCentres as $aoc) {
            if ($aoc->getIsInterim()) {
                $aoc->setIsInterim(false);
                $this->getRepo('ApplicationOperatingCentre')->save($aoc);
            }

            switch ($aoc->getAction()) {
                case 'A':
                    $this->addLicenceOperatingCentre($aoc, $licence);
                    $add++;
                    break;
                case 'U':
                    $loc = $this->findCorrespondingLoc($aoc, $licence);
                    if ($loc !== null) {
                        $this->updateLicenceOperatingCentre($aoc, $loc);
                        $update++;
                    }
                    break;
                case 'D':
                    if ($this->deleteLicenceOperatingCentre($aoc, $licence)) {
                        $delete++;
                    }
                    break;
            }
        }

        $result->addMessage($add . ' licence operating centre(s) created');
        $result->addMessage($update . ' licence operating centre(s) updated');
        $result->addMessage($delete . ' licence operating centre(s) removed');

        return $result;
    }

    /**
     * Find corresponding licence operating centre
     *
     * @param Aoc     $aoc     app operating centre
     * @param Licence $licence licence
     *
     * @return Loc
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    protected function findCorrespondingLoc(Aoc $aoc, Licence $licence)
    {
        return $this->getRepo('ApplicationOperatingCentre')->findCorrespondingLoc($aoc, $licence);
    }

    /**
     * Add licence operating centre
     *
     * @param Aoc     $aoc     app operating centre
     * @param Licence $licence licence
     *
     * @return void
     */
    protected function addLicenceOperatingCentre(Aoc $aoc, Licence $licence)
    {
        $loc = new Loc($licence, $aoc->getOperatingCentre());
        $licence->addOperatingCentres($loc);
        $this->updateLicenceOperatingCentre($aoc, $loc);
    }

    /**
     * Delete licence operating centre
     *
     * @param Aoc     $aoc     app operating centre
     * @param Licence $licence licence
     *
     * @return bool
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    protected function deleteLicenceOperatingCentre(Aoc $aoc, Licence $licence)
    {
        $loc = $this->findCorrespondingLoc($aoc, $licence);
        if ($loc === null) {
            // licence operating centre can be already deleted in case of other variation with schedule 4/1
            return false;
        }
        $this->getRepo('LicenceOperatingCentre')->delete($loc);

        // Side effects:
        // the system removes any undertakings or conditions attached to that operating centre
        // delinks them from a transport manager
        // removes the operating centre from any other applications
        $operatingCentre = $loc->getOperatingCentre();
        $licence = $loc->getLicence();
        $this->handleSideEffects(
            [
                DeleteConditionUndertakings::create(['operatingCentre' => $operatingCentre, 'licence' => $licence]),
                DeleteApplicationLinks::create(['operatingCentre' => $operatingCentre]),
            ]
        );
        return true;
    }

    /**
     * Update licence operating centre
     *
     * @param Aoc $aoc app operating centre
     * @param Loc $loc lic operating centre
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    protected function updateLicenceOperatingCentre(Aoc $aoc, Loc $loc)
    {
        $ignore = [
            'action',
            'isInterim',
            's4',
            'viAction'
        ];

        EntityCloner::cloneEntityInto($aoc, $loc, $ignore);

        $this->getRepo('LicenceOperatingCentre')->save($loc);
    }
}
