<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Command\TranslationKeyText\Create as CreateTranslationKeyTextCmd;
use Dvsa\Olcs\Api\Domain\Command\TranslationKeyText\Update as UpdateTranslationKeyTextCmd;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Service\EditorJs\LongTextConverterService;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
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
final class Update extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TranslationKey';
    protected $extraRepos = ['TranslationKeyText'];

    protected $createCmdClass = CreateTranslationKeyTextCmd::class;
    protected $updateCmdClass = UpdateTranslationKeyTextCmd::class;

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /**
         * @var UpdateTranslationKeyCmd $command
         * @var TranslationKeyEntity $translationKey
         * @var TranslationKeyRepo $repo
         */
        $repo = $this->getRepo();
        $translationKey = $repo->fetchById($command->getId());

        $format = $command->getFormat() ?? $translationKey->getFormat();
        if (!in_array($format, ['text', 'editorjs'], true)) {
            throw new ValidationException(['format' => 'Invalid translation format']);
        }

        if ($translationKey->getFormat() === 'editorjs' && $format !== 'editorjs') {
            throw new ValidationException(['format' => 'Rich translations cannot be changed to plain text']);
        }

        $translations = self::prepareTranslations($command->getTranslationsArray(), $format);

        if ($format === 'editorjs' && $translationKey->getFormat() !== 'editorjs') {
            foreach ($translationKey->getTranslationKeyTexts() as $existingText) {
                $locale = $existingText->getLanguage()->getIsoCode();
                if (!array_key_exists($locale, $command->getTranslationsArray())) {
                    throw new ValidationException(['translationsArray' => 'Every existing language must be converted before promotion']);
                }
            }
        }

        if ($command->getFormat() !== null && $command->getFormat() !== $translationKey->getFormat()) {
            $translationKey->setFormat($format);
            $repo->save($translationKey);
        }

        if ($command->getDescription() !== null) {
            $translationKey->setDescription($command->getDescription());
            $repo->save($translationKey);
        }

        $this->processTranslations($translations, $translationKey);

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
    public static function prepareTranslations(array $translationsArray, string $format): array
    {
        $prepared = [];
        foreach ($translationsArray as $isoCode => $translatedText) {
            $translatedText = base64_decode((string) $translatedText, true);
            if ($translatedText === false) {
                throw new ValidationException(['translationsArray' => 'Invalid encoded translation']);
            }
            if (array_key_exists($isoCode, Language::SUPPORTED_LANGUAGES)) {
                $contentJson = null;
                if ($format === 'editorjs') {
                    try {
                        $contentJson = json_decode($translatedText, true, flags: JSON_THROW_ON_ERROR);
                    } catch (\JsonException) {
                        throw new ValidationException(['translationsArray' => 'Invalid EditorJS JSON']);
                    }
                    if (!is_array($contentJson) || !isset($contentJson['blocks']) || !is_array($contentJson['blocks'])) {
                        throw new ValidationException(['translationsArray' => 'Invalid EditorJS document']);
                    }
                    foreach ($contentJson['blocks'] as $block) {
                        if (!is_array($block) || !isset($block['type'], $block['data']) || !is_array($block['data']) || !in_array($block['type'], ['paragraph', 'heading', 'header', 'list'], true)) {
                            throw new ValidationException(['translationsArray' => 'Invalid EditorJS block']);
                        }
                    }
                    try {
                        $translatedText = (new LongTextConverterService())->convertJsonToHtml($translatedText);
                    } catch (\Throwable $exception) {
                        throw new ValidationException(['translationsArray' => 'EditorJS content cannot be rendered: ' . $exception->getMessage()]);
                    }
                    $visible = html_entity_decode(strip_tags($translatedText), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    if (preg_replace('/[\s\x{00A0}]+/u', '', $visible) === '') {
                        throw new ValidationException(['translationsArray' => 'EditorJS content cannot be blank']);
                    }
                }
                $prepared[] = [Language::SUPPORTED_LANGUAGES[$isoCode]['id'], $translatedText, $contentJson];
            } else {
                throw new RuntimeException('Error processing translation. Invalid or unsupported language code');
            }
        }
        return $prepared;
    }

    protected function processTranslations(array $translations, $parentEntity): void
    {
        foreach ($translations as [$languageId, $translatedText, $contentJson]) {
            $this->updateOrCreate($parentEntity->getId(), $languageId, $translatedText, $contentJson);
        }
    }

    /**
     * @param int $parentEntityId
     */
    protected function updateOrCreate($parentEntityId, int $languageId, string $translatedText, ?array $contentJson = null)
    {
        $transRecord = $this->getRepo('TranslationKeyText')->fetchByParentLanguage($parentEntityId, $languageId);
        if (empty($transRecord)) {
            $this->result->merge($this->handleSideEffect(
                $this->createCmdClass::create(
                    [
                        'translationKey' => $parentEntityId,
                        'language' => $languageId,
                        'translatedText' => $translatedText,
                        ...($contentJson === null ? [] : ['contentJson' => $contentJson])
                    ]
                )
            ));
        } else {
            $this->result->merge($this->handleSideEffect(
                $this->updateCmdClass::create(
                    [
                        'id' => $transRecord->getId(),
                        'translatedText' => $translatedText,
                        ...($contentJson === null ? [] : ['contentJson' => $contentJson])
                    ]
                )
            ));
        }
    }
}
