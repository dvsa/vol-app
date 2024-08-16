<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cache;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\TranslationLoaderAwareInterface;
use Dvsa\Olcs\Api\Domain\TranslationLoaderAwareTrait;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Translation key by locale
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TranslationKey extends AbstractQueryHandler implements TranslationLoaderAwareInterface
{
    use TranslationLoaderAwareTrait;

    public function handleQuery(QueryInterface $query)
    {
        return $this->translationLoader->getMessagesFromDb(
            $query->getUniqueId(),
            TranslationLoader::DEFAULT_TEXT_DOMAIN
        );
    }
}
