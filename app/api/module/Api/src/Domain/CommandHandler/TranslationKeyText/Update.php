<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKeyText;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\System\TranslationKeyText;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\TranslationKeyText\Update as UpdateCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKeyText as TranslationKeyTextRepo;

/**
 * Update translation key text entry
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'TranslationKeyText';

    /**
     * Update translation text entry for a translation key
     *
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var TranslationKeyTextRepo  $translationKeyTextRepo
         * @var TranslationKeyText      $translationKeyText
         * @var UpdateCmd               $command
         */

        $translationKeyTextRepo = $this->getRepo('TranslationKeyText');
        $translationKeyText = $translationKeyTextRepo->fetchById($command->getId());

        $translationKeyText->update($command->getTranslatedText());
        $translationKeyTextRepo->save($translationKeyText);

        $this->result->addId('TranslationKeyText', $translationKeyText->getId());
        $this->result->addMessage('Translation Key Text Updated: ' . $translationKeyText->getId());

        return $this->result;
    }
}
