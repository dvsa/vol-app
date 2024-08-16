<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cache;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\TranslationLoaderAwareInterface;
use Dvsa\Olcs\Api\Domain\TranslationLoaderAwareTrait;
use Dvsa\Olcs\Api\Domain\Query\Cache\Replacements as ReplacementsQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Retrieve translation replacements from the DB
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Replacements extends AbstractQueryHandler implements TranslationLoaderAwareInterface
{
    use TranslationLoaderAwareTrait;

    /**
     * @param QueryInterface|ReplacementsQry $query
     * @return mixed
     */
    public function handleQuery(QueryInterface $query)
    {
        return $this->translationLoader->getReplacementsFromDb();
    }
}
