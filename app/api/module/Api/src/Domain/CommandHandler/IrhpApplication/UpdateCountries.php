<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries as UpdateCountriesCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\ApplicationAnswersClearer;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

/**
 * Update countries
 */
class UpdateCountries extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['IrhpPermitWindow', 'IrhpPermitApplication'];

    /** @var ApplicationAnswersClearer */
    private $applicationAnswersClearer;

    /**
     * Handle command
     *
     * @param UpdateCountriesCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $countries = $command->getCountries();

        if (empty($countries)) {
            throw new ValidationException(['At least one country must be selected.']);
        }

        $irhpApplicationId = $command->getId();

        /* @var $irhpApplicationRepo \Dvsa\Olcs\Api\Domain\Repository\IrhpApplication */
        $irhpApplicationRepo = $this->getRepo();

        /* @var $irhpApplication IrhpApplication */
        $irhpApplication = $irhpApplicationRepo->fetchById($irhpApplicationId);

        if (!$irhpApplication->canUpdateCountries()) {
            throw new ValidationException(['IRHP application cannot be updated.']);
        }

        // update list of countries linked to the application
        $irhpApplication->setCountrys(
            new ArrayCollection(
                array_map(
                    fn($countryId) => $this->getRepo()->getReference(Country::class, $countryId),
                    $countries
                )
            )
        );

        // get the list of existing IrhpPermitApplication indexed by window id
        $existingIrhpPermitAppsByWindowId = [];

        /* @var $irhpPermitApplication IrhpPermitApplication */
        foreach ($irhpApplication->getIrhpPermitApplications() as $irhpPermitApplication) {
            $existingIrhpPermitAppsByWindowId[$irhpPermitApplication->getIrhpPermitWindow()->getId()]
                = $irhpPermitApplication->getId();
        }

        /* @var $irhpPermitWindowRepo \Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow */
        $irhpPermitWindowRepo = $this->getRepo('IrhpPermitWindow');

        // fetch the list of all open windows for selected countries
        $openWindows = $irhpPermitWindowRepo->fetchOpenWindowsByCountry(
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
            $countries,
            new DateTime()
        );

        /* @var $irhpPermitApplicationRepo \Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication */
        $irhpPermitApplicationRepo = $this->getRepo('IrhpPermitApplication');

        // update the records
        $windowIdsToBeKept = [];

        /* @var $irhpPermitWindow IrhpPermitWindow */
        foreach ($openWindows as $irhpPermitWindow) {
            if (isset($existingIrhpPermitAppsByWindowId[$irhpPermitWindow->getId()])) {
                // the application already linked to the window - mark to be kept
                $windowIdsToBeKept[] = $irhpPermitWindow->getId();
            }
        }

        // delete no longer needed IrhpPermitApplication records
        /* @var $existingIrhpPermitApplication IrhpPermitApplication */
        foreach ($existingIrhpPermitAppsByWindowId as $windowId => $existingIrhpPermitAppId) {
            if (!in_array($windowId, $windowIdsToBeKept)) {
                $irhpPermitApplication = $irhpPermitApplicationRepo->fetchById($existingIrhpPermitAppId);

                // clear all existing q&a answers
                $this->applicationAnswersClearer->clear($irhpPermitApplication);
                // delete IrhpPermitApplication if the window is not to be kept
                $irhpPermitApplicationRepo->deleteOnFlush($irhpPermitApplication);
            }
        }

        // reset flags
        $irhpApplication->resetCheckAnswersAndDeclaration();
        $irhpApplicationRepo->saveOnFlush($irhpApplication);

        // save all the changes
        $irhpApplicationRepo->flushAll();

        $this->result->addId('irhpApplication', $irhpApplicationId);
        $this->result->addMessage('Countries updated for IRHP application');

        return $this->result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->applicationAnswersClearer = $container->get('QaApplicationAnswersClearer');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
