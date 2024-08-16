<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKeyText;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\System\TranslationKeyText;
use Dvsa\Olcs\Api\Entity\System\TranslationKey;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\TranslationKeyText\Create as CreateCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKeyText as TranslationKeyTextRepo;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKey as TranslationKeyRepo;
use Dvsa\Olcs\Api\Domain\Repository\Language as LanguageRepo;

/**
 * Create translation key text entry
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'TranslationKeyText';
    protected $extraRepos = ['Language', 'TranslationKey'];

    /**
     * Create translation text entry for a translation key
     *
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var LanguageRepo            $languageRepo
         * @var TranslationKeyRepo      $translationKeyRepo
         * @var TranslationKeyTextRepo  $translationKeyTextRepo
         * @var TranslationKey          $translationKey
         * @var CreateCmd               $command
         */

        $translationKeyTextRepo = $this->getRepo('TranslationKeyText');
        $translationKeyRepo = $this->getRepo('TranslationKey');
        $languageRepo = $this->getRepo('Language');

        $translationKey = $translationKeyRepo->fetchById($command->getTranslationKey());
        $language = $languageRepo->fetchById($command->getLanguage());

        $newTranslationKeyText = TranslationKeyText::create($language, $translationKey, $command->getTranslatedText());

        $translationKeyTextRepo->save($newTranslationKeyText);
        $newId = $newTranslationKeyText->getId();

        $this->result->addId('TranslationKeyText', $newId);
        $this->result->addMessage('Translation Key Text Created: ' . $newId);

        return $this->result;
    }
}
