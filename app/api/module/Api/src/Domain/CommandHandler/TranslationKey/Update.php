<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Command\TranslationKeyText\Create as CreateTranslationKeyTextCmd;
use Dvsa\Olcs\Api\Domain\Command\TranslationKeyText\Update as UpdateTranslationKeyTextCmd;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\System\Language;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TranslationKey\GenerateCache;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\TranslationKey as TranslationKeyEntity;
use Dvsa\Olcs\Transfer\Command\TranslationKey\Update as UpdateTranslationKeyCmd;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKey as TranslationKeyRepo;

/**
 * Update a Translation Key and child translations
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'TranslationKey';
    protected $extraRepos = ['TranslationKeyText'];

    protected $createCmdClass = CreateTranslationKeyTextCmd::class;
    protected $updateCmdClass = UpdateTranslationKeyTextCmd::class;

    public function handleCommand(CommandInterface $command): Result
    {
        /**
         * @var UpdateTranslationKeyCmd $command
         * @var TranslationKeyEntity $translationKey
         * @var TranslationKeyRepo $repo
         */
        $repo = $this->getRepo();
        $translationKey = $repo->fetchById($command->getId());

        $this->processTranslations($command->getTranslationsArray(), $translationKey);

        //refresh the translation cache
        $this->result->merge($this->handleSideEffect(GenerateCache::create([])));

        $this->result->addId('TranslationKey', $translationKey->getId());
        $this->result->addMessage('Translations Updated');

        return $this->result;
    }

    /**
     * @param array TranslationsArray
     * @param $parentEntity
     */
    protected function processTranslations(array $translationsArray, $parentEntity)
    {
        foreach ($translationsArray as $isoCode => $translatedText) {
            $translatedText = base64_decode((string) $translatedText);
            if (array_key_exists($isoCode, Language::SUPPORTED_LANGUAGES)) {
                $this->updateOrCreate($parentEntity->getId(), Language::SUPPORTED_LANGUAGES[$isoCode]['id'], $translatedText);
            } else {
                throw new RuntimeException('Error processing translation. Invalid or unsupported language code');
            }
        }
    }

    /**
     * @param int $parentEntityId
     */
    protected function updateOrCreate($parentEntityId, int $languageId, string $translatedText)
    {
        $transRecord = $this->getRepo('TranslationKeyText')->fetchByParentLanguage($parentEntityId, $languageId);
        if (empty($transRecord)) {
            $this->result->merge($this->handleSideEffect(
                $this->createCmdClass::create(
                    [
                        'translationKey' => $parentEntityId,
                        'language' => $languageId,
                        'translatedText' => $translatedText
                    ]
                )
            ));
        } else {
            $this->result->merge($this->handleSideEffect(
                $this->updateCmdClass::create(
                    [
                        'id' => $transRecord->getId(),
                        'translatedText' => $translatedText
                    ]
                )
            ));
        }
    }
}
