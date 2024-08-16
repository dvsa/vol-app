<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries;

class ApplicationCountryRemover
{
    /**
     * Create service instance
     *
     *
     * @return ApplicationCountryRemover
     */
    public function __construct(private readonly CommandCreator $commandCreator, private readonly CommandHandlerManager $commandHandlerManager)
    {
    }

    /**
     * Remove all traces of a country from an application
     */
    public function remove(IrhpPermitApplication $irhpPermitApplication)
    {
        $countryCodeForDeletion = $irhpPermitApplication->getIrhpPermitWindow()
            ->getIrhpPermitStock()
            ->getCountry()
            ->getId();

        $commandCountries = [];

        $irhpApplication = $irhpPermitApplication->getIrhpApplication();
        $countries = $irhpApplication->getCountrys();

        foreach ($countries as $country) {
            $countryCode = $country->getId();
            if ($countryCode != $countryCodeForDeletion) {
                $commandCountries[] = $countryCode;
            }
        }

        $commandParams = [
            'id' => $irhpApplication->getId(),
            'countries' => $commandCountries
        ];

        $command = $this->commandCreator->create(UpdateCountries::class, $commandParams);
        $this->commandHandlerManager->handleCommand($command, false);
    }
}
