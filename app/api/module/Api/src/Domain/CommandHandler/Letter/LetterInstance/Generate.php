<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceAppendix;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceChoice;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\Generate as Cmd;

/**
 * Generate LetterInstance
 *
 * Creates a new letter instance from a letter type and user selections.
 */
final class Generate extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterInstance';

    protected $extraRepos = [
        'LetterType',
        'LetterIssue',
        'LetterAppendix',
        'LetterChoice',
        'Licence',
        'Application',
        'Cases',
        'BusReg',
        'TransportManager',
        'IrhpApplication',
        'Organisation',
    ];

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        // Create the letter instance
        $letterInstance = new LetterInstanceEntity();

        // Generate reference if not provided
        $reference = LetterInstanceEntity::generateReference();
        $letterInstance->setReference($reference);

        // Set letter type (required)
        $letterType = $this->getRepo('LetterType')->fetchById($command->getLetterType());
        $letterInstance->setLetterType($letterType);

        // Set status to DRAFT
        $status = $this->getRepo()->getRefdataReference(LetterInstanceEntity::STATUS_DRAFT);
        $letterInstance->setStatus($status);

        // Set optional relations (licence, application, case, etc.)
        $this->setOptionalRelations($letterInstance, $command);

        // Build context for variant resolution
        $context = $this->buildVariantContext($letterInstance, $command);

        // Populate instance sections from letter type assembly, resolving variants
        $unresolvedRequiredSections = [];

        foreach ($letterType->getLetterTypeSections() ?? [] as $typeSection) {
            $section = $typeSection->getLetterSection();
            $variant = $section->getVariantForContext($context);

            if ($variant === null || $variant->getCurrentVersion() === null) {
                // Section was skipped -- check if it was required
                if ($typeSection->getIsRequired()) {
                    $unresolvedRequiredSections[] = $section->getName() ?? $section->getSectionKey();
                }
                continue;
            }

            $instanceSection = new LetterInstanceSection();
            $instanceSection->setLetterInstance($letterInstance);
            $instanceSection->setLetterSectionVersion($variant->getCurrentVersion());
            $instanceSection->setDisplayOrder($typeSection->getDisplayOrder());
            $letterInstance->addLetterInstanceSection($instanceSection);
        }

        // Warn about any required sections that couldn't be resolved
        if (!empty($unresolvedRequiredSections)) {
            foreach ($unresolvedRequiredSections as $sectionName) {
                $this->result->addMessage(
                    'Required section "' . $sectionName . '" could not be included — no matching variant for the current context'
                );
            }
            $this->result->setFlag('hasRequiredSectionWarnings', true);
        }

        // Create instance issues from selected issues
        if (!empty($command->getSelectedIssues())) {
            $displayOrder = 0;
            foreach ($command->getSelectedIssues() as $issueId) {
                $letterIssue = $this->getRepo('LetterIssue')->fetchById($issueId);
                $issueVersion = $letterIssue->getCurrentVersion();

                if ($issueVersion) {
                    $instanceIssue = new LetterInstanceIssue();
                    $instanceIssue->setLetterInstance($letterInstance);
                    $instanceIssue->setLetterIssueVersion($issueVersion);
                    $instanceIssue->setDisplayOrder($displayOrder++);

                    $letterInstance->addLetterInstanceIssue($instanceIssue);
                }
            }
        }

        // Create instance appendices from selected appendices
        if (!empty($command->getSelectedAppendices())) {
            $displayOrder = 0;
            foreach ($command->getSelectedAppendices() as $appendixId) {
                $letterAppendix = $this->getRepo('LetterAppendix')->fetchById($appendixId);
                $appendixVersion = $letterAppendix->getCurrentVersion();

                if ($appendixVersion) {
                    $instanceAppendix = new LetterInstanceAppendix();
                    $instanceAppendix->setLetterInstance($letterInstance);
                    $instanceAppendix->setLetterAppendixVersion($appendixVersion);
                    $instanceAppendix->setDisplayOrder($displayOrder++);

                    $letterInstance->addLetterInstanceAppendix($instanceAppendix);
                }
            }
        }

        // Record selected letter choices
        if (!empty($command->getSelectedChoices())) {
            foreach ($command->getSelectedChoices() as $choiceId) {
                $letterChoice = $this->getRepo('LetterChoice')->fetchById($choiceId);
                $instanceChoice = new LetterInstanceChoice();
                $instanceChoice->setLetterInstance($letterInstance);
                $instanceChoice->setLetterChoice($letterChoice);
                $letterInstance->addLetterInstanceChoice($instanceChoice);
            }
        }

        // Save the letter instance with all its related entities
        $this->getRepo()->save($letterInstance);

        $this->result->addId('letterInstance', $letterInstance->getId());
        $this->result->addMessage("Letter instance '{$reference}' generated successfully");

        return $this->result;
    }

    /**
     * Build context array for variant resolution
     *
     * @param LetterInstanceEntity $letterInstance
     * @param Cmd $command
     * @return array
     */
    private function buildVariantContext(LetterInstanceEntity $letterInstance, Cmd $command): array
    {
        $application = $letterInstance->getApplication();
        $licence = $letterInstance->getLicence();

        $organisation = $letterInstance->getOrganisation();

        return [
            'goodsOrPsv' => $application?->getGoodsOrPsv()?->getId()
                ?? $licence?->getGoodsOrPsv()?->getId(),
            'isVariation' => $application ? (bool) $application->getIsVariation() : null,
            'isNi' => $licence ? $licence->isNi() : null,
            'organisationType' => $organisation?->getType()?->getId(),
            'selectedChoiceIds' => $command->getSelectedChoices() ?? [],
        ];
    }

    /**
     * Set optional relations on the letter instance
     *
     * @param LetterInstanceEntity $letterInstance
     * @param Cmd $command
     * @return void
     */
    private function setOptionalRelations(LetterInstanceEntity $letterInstance, Cmd $command): void
    {
        if ($command->getLicence() !== null) {
            $licence = $this->getRepo('Licence')->fetchById($command->getLicence());
            $letterInstance->setLicence($licence);

            // Set recipient organisation from licence
            $organisation = $licence->getOrganisation();
            if ($organisation) {
                $letterInstance->setOrganisation($organisation);
            }
        }

        if ($command->getApplication() !== null) {
            $application = $this->getRepo('Application')->fetchById($command->getApplication());
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

        if ($command->getCase() !== null) {
            $case = $this->getRepo('Cases')->fetchById($command->getCase());
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

        if ($command->getBusReg() !== null) {
            $busReg = $this->getRepo('BusReg')->fetchById($command->getBusReg());
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

        if ($command->getTransportManager() !== null) {
            $transportManager = $this->getRepo('TransportManager')->fetchById($command->getTransportManager());
            $letterInstance->setTransportManager($transportManager);
        }

        if ($command->getIrhpApplication() !== null) {
            $irhpApplication = $this->getRepo('IrhpApplication')->fetchById($command->getIrhpApplication());
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

        if ($command->getIrfoOrganisation() !== null) {
            $irfoOrganisation = $this->getRepo('Organisation')->fetchById($command->getIrfoOrganisation());
            $letterInstance->setIrfoOrganisation($irfoOrganisation);
        }
    }
}
