<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\System\TranslationKey;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TranslationKey\Create as CreateTranslationKeyCmd;
use Dvsa\Olcs\Transfer\Command\TranslationKey\Update as UpdateTranslationKeyCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Create Translation key
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'TranslationKey';

    /**
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var CreateTranslationKeyCmd $command
         */
        $repo = $this->getRepo('TranslationKey');

        $translationKey = TranslationKey::create($command->getTranslationKey(), $command->getDescription());
        try {
            $repo->save($translationKey);
        } catch (\Exception) {
            throw new NotFoundException('editable-translations-cant-save');
        }

        $this->result->addId('TranslationKey', $command->getTranslationKey());
        $this->result->addMessage('TranslationKey created');

        $cmdData = array_merge($command->getArrayCopy(), ['id' => $translationKey->getId()]);

        // Add the translation Texts to the newly created key, this also regenerates the redis cache.
        $this->handleSideEffect(UpdateTranslationKeyCmd::create($cmdData));

        return $this->result;
    }
}
