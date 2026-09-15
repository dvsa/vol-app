<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceChoice;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer;
use Dvsa\Olcs\Api\Service\Letter\SectionVariantResolver;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\Generate as Cmd;
use Psr\Container\ContainerInterface;

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

    private SectionVariantResolver $sectionVariantResolver;
    private LetterInstanceComposer $letterInstanceComposer;

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->sectionVariantResolver = $container->get(SectionVariantResolver::class);
        $this->letterInstanceComposer = $container->get(LetterInstanceComposer::class);
        return parent::__invoke($container, $requestedName, $options);
    }

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
        $resolution = $this->sectionVariantResolver->resolveForLetterType($letterType, $context);

        $this->letterInstanceComposer->composeSections($letterInstance, $resolution);

        // Warn about any sections that couldn't be resolved
        foreach ($resolution->getUnresolvedRequired() as $unresolvedSection) {
            $this->result->addMessage(
                'Required section "' . $unresolvedSection->getSectionName() . '" could not be included — no matching variant for the current context'
            );
        }

        foreach ($resolution->getUnresolvedOptional() as $unresolvedSection) {
            $this->result->addMessage(
                'Optional section "' . $unresolvedSection->getSectionName() . '" could not be included — no matching variant for the current context'
            );
        }

        if ($resolution->hasUnresolved()) {
            $this->result->setFlag('hasRequiredSectionWarnings', true);
        }

        // Create instance issues from selected issues
        $issueVersions = [];
        foreach ($command->getSelectedIssues() ?? [] as $issueId) {
            $issueVersion = $this->getRepo('LetterIssue')->fetchById($issueId)->getCurrentVersion();
            if ($issueVersion) {
                $issueVersions[] = $issueVersion;
            }
        }
        $this->letterInstanceComposer->composeIssues($letterInstance, $issueVersions);

        $this->letterInstanceComposer->composeTodos($letterInstance);

        // Create instance appendices from selected appendices
        $appendixVersions = [];
        foreach ($command->getSelectedAppendices() ?? [] as $appendixId) {
            $appendixVersion = $this->getRepo('LetterAppendix')->fetchById($appendixId)->getCurrentVersion();
            if ($appendixVersion) {
                $appendixVersions[] = $appendixVersion;
            }
        }
        $this->letterInstanceComposer->composeAppendices($letterInstance, $appendixVersions);

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
