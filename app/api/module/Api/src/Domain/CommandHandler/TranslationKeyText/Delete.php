<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKeyText;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\TranslationKey\GenerateCache;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete a Translation Key Text record
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Delete extends AbstractCommandHandler
{
    protected $repoServiceName = 'TranslationKeyText';

    /**
     * Delete Command Handler
     *
     * @param CommandInterface $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        $id = $command->getId();
        $translationKeyText = $this->getRepo()->fetchById($id);

        $this->getRepo()->delete($translationKeyText);
        $this->result->addId('id', $id);
        $this->result->addMessage(sprintf('Translation Key Text %s Deleted', $id));

        //refresh the translation cache
        $this->result->merge($this->handleSideEffect(GenerateCache::create([])));

        return $this->result;
    }
}
