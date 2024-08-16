<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use DateTime;
use Dvsa\Olcs\Cli\Domain\Command\Permits\CancelUnsubmittedBilateral as CancelUnsubmittedBilateralCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CancelApplication as CancelApplicationCmd;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries as UpdateCountriesCmd;

/**
 * Cancel unsubmitted bilateral
 */
class CancelUnsubmittedBilateral extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['IrhpPermitWindow'];

    /**
     * Handle command
     *
     * @param CancelUnsubmittedBilateralCmd $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $openWindows = $this->generateOpenWindows();

        $irhpApplications = $this->getRepo()->fetchNotYetSubmittedBilateralApplications();
        $this->result->addMessage(count($irhpApplications) . ' unsubmitted bilateral applications found');

        foreach ($irhpApplications as $irhpApplication) {
            $this->handleIrhpApplication($irhpApplication, $openWindows);
        }

        return $this->result;
    }

    /**
     * Return an array containing details of the open bilateral window ids for each country
     *
     * @return array
     */
    private function generateOpenWindows()
    {
        $openIrhpPermitWindows = $this->getRepo('IrhpPermitWindow')->fetchOpenWindowsByType(
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
            new DateTime(),
            true
        );

        $openWindows = [];

        foreach ($openIrhpPermitWindows as $openIrhpPermitWindow) {
            $countryId = $openIrhpPermitWindow->getIrhpPermitStock()->getCountry()->getId();

            if (!isset($openWindows[$countryId])) {
                $openWindows[$countryId] = [];
            }

            $openWindows[$countryId][$openIrhpPermitWindow->getId()] = true;
        }

        return $openWindows;
    }

    /**
     * Update the countries as appropriate with an application using the provided open windows information
     */
    private function handleIrhpApplication(IrhpApplication $irhpApplication, array $openWindows)
    {
        $existingCountryIds = $irhpApplication->getCountryIds();
        $retainedCountryIds = [];

        foreach ($existingCountryIds as $existingCountryId) {
            $shouldRetainCountry = $this->shouldIrhpApplicationRetainCountry(
                $irhpApplication,
                $openWindows,
                $existingCountryId
            );

            if ($shouldRetainCountry) {
                $retainedCountryIds[] = $existingCountryId;
            }
        }

        $irhpApplicationId = $irhpApplication->getId();

        if (empty($retainedCountryIds)) {
            $command = CancelApplicationCmd::create([
                'id' => $irhpApplicationId
            ]);

            $message = sprintf(
                'Cancelled irhp application %s',
                $irhpApplicationId
            );

            $this->handleSideEffect($command);
            $this->result->addMessage($message);
        } elseif (count($retainedCountryIds) < count($existingCountryIds)) {
            $command = UpdateCountriesCmd::create([
                'id' => $irhpApplicationId,
                'countries' => $retainedCountryIds,
            ]);

            $message = sprintf(
                'Updated countries to %s on irhp application %s',
                implode(', ', $retainedCountryIds),
                $irhpApplicationId
            );

            $this->handleSideEffect($command);
            $this->result->addMessage($message);
        }
    }

    /**
     * Whether the specified existing country should be retained within the application
     *
     * @param string $countryId
     */
    private function shouldIrhpApplicationRetainCountry(
        IrhpApplication $irhpApplication,
        array $openWindows,
        $countryId
    ) {
        $shouldRetainCountry = false;

        if (isset($openWindows[$countryId])) {
            $shouldRetainCountry = true;
            $irhpPermitApplication = $irhpApplication->getIrhpPermitApplicationByCountryId($countryId);

            if (is_object($irhpPermitApplication)) {
                $irhpPermitApplicationWindowId = $irhpPermitApplication->getIrhpPermitWindow()->getId();
                if (!isset($openWindows[$countryId][$irhpPermitApplicationWindowId])) {
                    $shouldRetainCountry = false;
                }
            }
        }

        return $shouldRetainCountry;
    }
}
