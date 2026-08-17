<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterSectionVariant;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant as LetterSectionVariantEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSectionVariant\Create as Cmd;

/**
 * Create LetterSectionVariant
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterSectionVariant';

    protected $extraRepos = ['LetterSection', 'LetterChoice'];

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        // Set the parent letter section
        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterSection $letterSection */
        $letterSection = $this->getRepo('LetterSection')->fetchById($command->getSectionId());

        $requestedGoodsOrPsv = $command->getGoodsOrPsv() ?: null;
        $requestedOrgType = $command->getOrganisationType() ?: null;
        $requestedChoice = $command->getLetterChoice() ? (int) $command->getLetterChoice() : null;

        $requestIsDefault = $requestedGoodsOrPsv === null
            && $command->getIsVariation() === null
            && $command->getIsNi() === null
            && $requestedOrgType === null
            && $requestedChoice === null;

        // Check for duplicate condition combination. Soft-deleted variants are ignored so that a
        // combination an admin deleted can be recreated; without this, deleting a variant left the
        // admin unable to add it back.
        foreach ($letterSection->getVariants() as $existing) {
            if ($existing->isDeleted()) {
                continue;
            }

            // An all-null request is a second default. The unique key cannot catch these because
            // MySQL treats NULLs as distinct, so it has to be rejected here.
            if ($existing->isDefault()) {
                if ($requestIsDefault) {
                    throw new \Dvsa\Olcs\Api\Domain\Exception\BadRequestException(
                        'A default variant already exists for this section'
                    );
                }
                continue;
            }

            $sameGoodsOrPsv = ($existing->getGoodsOrPsv()?->getId() ?? null) === $requestedGoodsOrPsv;
            $sameIsVariation = $existing->getIsVariation() === $command->getIsVariation();
            $sameIsNi = $existing->getIsNi() === $command->getIsNi();
            $sameOrgType = ($existing->getOrganisationType()?->getId() ?? null) === $requestedOrgType;
            $sameChoice = ($existing->getLetterChoice()?->getId() ?? null) === $requestedChoice;

            if ($sameGoodsOrPsv && $sameIsVariation && $sameIsNi && $sameOrgType && $sameChoice) {
                throw new \Dvsa\Olcs\Api\Domain\Exception\BadRequestException(
                    'A variant with these exact conditions already exists'
                );
            }
        }

        $variant = new LetterSectionVariantEntity();
        $variant->setLetterSection($letterSection);

        // Set condition fields (all nullable - null means "matches any")
        if ($command->getGoodsOrPsv() !== null) {
            $variant->setGoodsOrPsv($this->getRepo()->getRefdataReference($command->getGoodsOrPsv()));
        }

        if ($command->getIsVariation() !== null) {
            $variant->setIsVariation($command->getIsVariation());
        }

        if ($command->getIsNi() !== null) {
            $variant->setIsNi($command->getIsNi());
        }

        if ($command->getOrganisationType() !== null) {
            $variant->setOrganisationType($this->getRepo()->getRefdataReference($command->getOrganisationType()));
        }

        if ($command->getLetterChoice() !== null) {
            /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterChoice $letterChoice */
            $letterChoice = $this->getRepo('LetterChoice')->fetchById($command->getLetterChoice());
            $variant->setLetterChoice($letterChoice);
        }

        // Create initial LetterSectionVersion with content
        $version = new LetterSectionVersion();
        $version->setLetterSectionVariant($variant);
        $version->setVersionNumber(1);
        $version->setName($letterSection->getName());
        $version->setSectionType($letterSection->getSectionType());

        // Use provided content, or copy from the section's default variant current version
        if ($command->getDefaultContent() !== null) {
            $version->setDefaultContent($command->getDefaultContent());
        } else {
            // Copy content from the section's current default content
            $version->setDefaultContent($letterSection->getDefaultContent());
        }

        $variant->addVersion($version);

        // Save variant first to get an ID
        $this->getRepo()->save($variant);

        // Set as current version
        $variant->setCurrentVersion($version);
        $this->getRepo()->save($variant);

        $this->result->addId('letterSectionVariant', $variant->getId());
        $this->result->addMessage('Letter section variant created');

        return $this->result;
    }
}
