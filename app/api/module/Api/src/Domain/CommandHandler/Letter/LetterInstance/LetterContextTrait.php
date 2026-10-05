<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\Generate as GenerateCmd;
use Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\GenerationContext as GenerationContextQry;

/**
 * Links a letter to the entity it is for and works out its Goods/PSV and NI context.
 * Shared with the GenerationContext query so the content options screen and generation agree.
 */
trait LetterContextTrait
{
    /**
     * Goods/PSV and NI for the letter. Goods/PSV comes from the application first, then the licence
     *
     * @param LetterInstanceEntity $letterInstance
     * @return array{goodsOrPsv: ?string, isNi: ?bool}
     */
    private function goodsOrPsvAndNi(LetterInstanceEntity $letterInstance): array
    {
        $application = $letterInstance->getApplication();
        $licence = $letterInstance->getLicence();

        return [
            'goodsOrPsv' => $application?->getGoodsOrPsv()?->getId()
                ?? $licence?->getGoodsOrPsv()?->getId(),
            'isNi' => $licence ? $licence->isNi() : null,
        ];
    }

    /**
     * Set optional relations on the letter instance
     *
     * @param LetterInstanceEntity $letterInstance
     * @param GenerateCmd|GenerationContextQry $dto
     * @return void
     */
    private function setOptionalRelations(LetterInstanceEntity $letterInstance, GenerateCmd|GenerationContextQry $dto): void
    {
        if ($dto->getLicence() !== null) {
            $licence = $this->getRepo('Licence')->fetchById($dto->getLicence());
            $letterInstance->setLicence($licence);

            // Set recipient organisation from licence
            $organisation = $licence->getOrganisation();
            if ($organisation) {
                $letterInstance->setOrganisation($organisation);
            }
        }

        if ($dto->getApplication() !== null) {
            $application = $this->getRepo('Application')->fetchById($dto->getApplication());
            $letterInstance->setApplication($application);

            // Set licence from application (if not already set by the licence block above)
            $licence = $application->getLicence();
            if ($licence) {
                if ($letterInstance->getLicence() === null) {
                    $letterInstance->setLicence($licence);
                }

                $organisation = $licence->getOrganisation();
                if ($organisation) {
                    $letterInstance->setOrganisation($organisation);
                }
            }
        }

        if ($dto->getCase() !== null) {
            $case = $this->getRepo('Cases')->fetchById($dto->getCase());
            $letterInstance->setCase($case);

            // Set licence (and application) from case's relationships
            $licence = $case->getLicence();
            if ($licence) {
                if ($letterInstance->getLicence() === null) {
                    $letterInstance->setLicence($licence);
                }

                $organisation = $licence->getOrganisation();
                if ($organisation) {
                    $letterInstance->setOrganisation($organisation);
                }
            } elseif ($case->getApplication()) {
                $application = $case->getApplication();
                if ($letterInstance->getApplication() === null) {
                    $letterInstance->setApplication($application);
                }

                $licence = $application->getLicence();
                if ($licence) {
                    if ($letterInstance->getLicence() === null) {
                        $letterInstance->setLicence($licence);
                    }

                    $organisation = $licence->getOrganisation();
                    if ($organisation) {
                        $letterInstance->setOrganisation($organisation);
                    }
                }
            }
        }

        if ($dto->getBusReg() !== null) {
            $busReg = $this->getRepo('BusReg')->fetchById($dto->getBusReg());
            $letterInstance->setBusReg($busReg);

            // Set licence from bus registration
            $licence = $busReg->getLicence();
            if ($licence) {
                if ($letterInstance->getLicence() === null) {
                    $letterInstance->setLicence($licence);
                }

                $organisation = $licence->getOrganisation();
                if ($organisation) {
                    $letterInstance->setOrganisation($organisation);
                }
            }
        }

        if ($dto->getTransportManager() !== null) {
            $transportManager = $this->getRepo('TransportManager')->fetchById($dto->getTransportManager());
            $letterInstance->setTransportManager($transportManager);
        }

        if ($dto->getIrhpApplication() !== null) {
            $irhpApplication = $this->getRepo('IrhpApplication')->fetchById($dto->getIrhpApplication());
            $letterInstance->setIrhpApplication($irhpApplication);

            // Set licence from IRHP application
            $licence = $irhpApplication->getLicence();
            if ($licence) {
                if ($letterInstance->getLicence() === null) {
                    $letterInstance->setLicence($licence);
                }

                $organisation = $licence->getOrganisation();
                if ($organisation) {
                    $letterInstance->setOrganisation($organisation);
                }
            }
        }

        if ($dto->getIrfoOrganisation() !== null) {
            $irfoOrganisation = $this->getRepo('Organisation')->fetchById($dto->getIrfoOrganisation());
            $letterInstance->setIrfoOrganisation($irfoOrganisation);
        }
    }
}
