<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\TranslatorAwareInterface;
use Dvsa\Olcs\Api\Domain\TranslatorAwareTrait;
use Dvsa\Olcs\Api\Entity\System\Language;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;

/**
 * Create the translation key cache
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class GenerateCache extends AbstractCommandHandler implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public const string UPDATE_MSG = 'Translation key cache updated for %s';

    public function handleCommand(CommandInterface $command)
    {
        foreach (array_keys(Language::SUPPORTED_LANGUAGES) as $locale) {
            $this->result->merge(
                $this->generateCache(CacheEncryption::TRANSLATION_KEY_IDENTIFIER, $locale)
            );

            //clear the laminas translation cache, laminas will recreate based on the new translation loader contents
            $this->translator->clearCache(TranslationLoader::DEFAULT_TEXT_DOMAIN, $locale);

            $this->result->addMessage(sprintf(self::UPDATE_MSG, $locale));
        }

        return $this->result;
    }
}
