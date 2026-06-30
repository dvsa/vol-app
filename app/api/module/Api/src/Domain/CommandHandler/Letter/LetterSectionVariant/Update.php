<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterSectionVariant;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSectionVariant\Update as Cmd;

/**
 * Update LetterSectionVariant
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterSectionVariant';

    protected $extraRepos = ['LetterChoice'];

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant $variant */
        $variant = $this->getRepo()->fetchUsingId($command);

        // Update condition fields
        if ($command->getGoodsOrPsv() !== null) {
            $variant->setGoodsOrPsv($this->getRepo()->getRefdataReference($command->getGoodsOrPsv()));
        } else {
            $variant->setGoodsOrPsv(null);
        }

        if ($command->getIsVariation() !== null) {
            $variant->setIsVariation($command->getIsVariation());
        } else {
            $variant->setIsVariation(null);
        }

        if ($command->getIsNi() !== null) {
            $variant->setIsNi($command->getIsNi());
        } else {
            $variant->setIsNi(null);
        }

        if ($command->getOrganisationType() !== null) {
            $variant->setOrganisationType($this->getRepo()->getRefdataReference($command->getOrganisationType()));
        } else {
            $variant->setOrganisationType(null);
        }

        if ($command->getLetterChoice() !== null) {
            /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterChoice $letterChoice */
            $letterChoice = $this->getRepo('LetterChoice')->fetchById($command->getLetterChoice());
            $variant->setLetterChoice($letterChoice);
        } else {
            $variant->setLetterChoice(null);
        }

        // If defaultContent is provided and differs from current, create a new version
        if ($command->getDefaultContent() !== null) {
            $currentVersion = $variant->getCurrentVersion();
            $currentContent = $currentVersion ? $currentVersion->getDefaultContent() : null;

            if ($currentContent !== $command->getDefaultContent()) {
                $newVersionNumber = $currentVersion ? $currentVersion->getVersionNumber() + 1 : 1;

                $newVersion = new LetterSectionVersion();
                $newVersion->setLetterSectionVariant($variant);
                $newVersion->setVersionNumber($newVersionNumber);
                $newVersion->setDefaultContent($command->getDefaultContent());

                // Copy other fields from current version
                if ($currentVersion) {
                    $newVersion->setName($currentVersion->getName());
                    $newVersion->setSectionType($currentVersion->getSectionType());
                    $newVersion->setHelpText($currentVersion->getHelpText());
                    $newVersion->setMinLength($currentVersion->getMinLength());
                    $newVersion->setMaxLength($currentVersion->getMaxLength());
                    $newVersion->setIsLocked(false);
                    $newVersion->setRequiresInput($currentVersion->getRequiresInput());
                    $newVersion->setIsNi($currentVersion->getIsNi());
                    $newVersion->setGoodsOrPsv($currentVersion->getGoodsOrPsv());
                }

                $variant->addVersion($newVersion);
                $variant->setCurrentVersion($newVersion);
            }
        }

        $this->getRepo()->save($variant);

        $this->result->addId('letterSectionVariant', $variant->getId());
        $this->result->addMessage('Letter section variant updated');

        return $this->result;
    }
}
