<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Replacement;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Replacement\Update as ReplacementUpdateCmd;
use Dvsa\Olcs\Api\Entity\System\Replacement as ReplacementEntity;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;

/**
 * Update a Replacement
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'Replacement';

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        /**
         * @var ReplacementUpdateCmd $command
         * @var ReplacementEntity $replacement
         */
        $replacement = $this->getRepo()->fetchUsingId($command);

        $replacement->update(
            $command->getPlaceholder(),
            $command->getReplacementText()
        );

        $this->getRepo()->save($replacement);

        $this->result->addId('Replacement', $replacement->getId());
        $this->result->addMessage("Replacement '{$replacement->getId()}' updated");

        $this->result->merge($this->generateCache(CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER));

        return $this->result;
    }
}
