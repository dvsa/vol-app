<?php

/**
 * Generate Permit Documents
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermit\GenerateCoverLetterDocument;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermit\GeneratePermitDocument;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * Generate a Permit in RTF form
 *
 * @author Henry White <henry.white@capgemini.com>
 */
final class GeneratePermitDocuments extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpPermit';

    /**
     * @var array
     */
    private $coverLetterLicenceIds = [];

    /**
     * Handle command
     *
     * @param CommandInterface $command Command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        foreach ($command->getIds() as $id) {
            $irhpPermit = $this->getRepo()->fetchById($id, Query::HYDRATE_OBJECT);

            // generate cover letter
            $this->generateCoverLetter($irhpPermit);

            // generate permit
            $this->generatePermit($irhpPermit);
        }

        return $this->result;
    }

    /**
     * Generate cover letter document
     *
     * @param IrhpPermitEntity $irhpPermit IRHP Permit
     *
     * @return void
     */
    private function generateCoverLetter(IrhpPermitEntity $irhpPermit)
    {
        $irhpPermitApplication = $irhpPermit->getIrhpPermitApplication();

        $irhpPermitStock = $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock();
        $irhpPermitType = $irhpPermitStock->getIrhpPermitType();

        if (
            $irhpPermitType->isBilateral()
            && in_array(
                $irhpPermitStock->getCountry()->getId(),
                [
                    CountryEntity::ID_BELARUS,
                    CountryEntity::ID_GEORGIA,
                    CountryEntity::ID_KAZAKHSTAN,
                    CountryEntity::ID_MOROCCO,
                    CountryEntity::ID_RUSSIA,
                    CountryEntity::ID_TUNISIA,
                    CountryEntity::ID_TURKEY,
                    CountryEntity::ID_UKRAINE,
                ]
            )
        ) {
            // don't print cover letter for some countries
            return;
        }

        if (
            $irhpPermitType->isBilateral() || $irhpPermitType->isMultilateral() || $irhpPermitType->isEcmtShortTerm()
            || $irhpPermitType->isEcmtRemoval()
        ) {
            $licenceId = $irhpPermitApplication->getIrhpApplication()->getLicence()->getId();

            if (isset($this->coverLetterLicenceIds[$licenceId])) {
                // only one cover letter per licence required
                return;
            }

            $this->coverLetterLicenceIds[$licenceId] = true;
        }

        $this->result->merge(
            $this->handleSideEffect(
                GenerateCoverLetterDocument::create(
                    [
                        'irhpPermit' => $irhpPermit->getId(),
                    ]
                )
            ),
            true
        );
    }

    /**
     * Generate permit document
     *
     * @param IrhpPermitEntity $irhpPermit IRHP Permit
     *
     * @return void
     */
    private function generatePermit(IrhpPermitEntity $irhpPermit)
    {
        $this->result->merge(
            $this->handleSideEffect(
                GeneratePermitDocument::create(
                    [
                        'irhpPermit' => $irhpPermit->getId(),
                    ]
                )
            ),
            true
        );
    }
}
