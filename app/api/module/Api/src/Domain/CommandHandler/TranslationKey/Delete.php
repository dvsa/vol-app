<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\TranslationKey\GenerateCache;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\System\TranslationKey;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete an Translation Key
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Delete extends AbstractCommandHandler
{
    protected $repoServiceName = 'TranslationKey';

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
        $translationKey = $this->getRepo()->fetchById($id);

        /** @var TranslationKey $translationKey */
        if (!$translationKey->canDelete()) {
            throw new ValidationException(['editable-translations-cant-delete-with-texts']);
        }

        $this->getRepo()->delete($translationKey);
        $this->result->addId('id', $id);
        $this->result->addMessage(sprintf('Translation Key %s Deleted', $id));

        //refresh the translation cache
        $this->result->merge($this->handleSideEffect(GenerateCache::create([])));

        return $this->result;
    }
}
